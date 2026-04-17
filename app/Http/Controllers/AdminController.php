<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Log;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cession;
use App\Models\Produit;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    private function ensureAdmin()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    public function dashboard()
    {
        $this->ensureAdmin();

        // Statistiques des utilisateurs
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        // Niveaux de stock par produit
        $stockLevels = Produit::with('cuves')->get()->map(function ($produit) {
            $totalStock = $produit->cuves->sum('niveau_actuel');
            $totalCapacity = $produit->cuves->sum('capacite_totale');
            $percentage = $totalCapacity > 0 ? ($totalStock / $totalCapacity) * 100 : 0;

            return [
                'nom' => $produit->nom,
                'stock' => $totalStock,
                'percentage' => round($percentage)
            ];
        });

        // Dernières opérations (mélange de dépôts et chargements)
        $depotages = Depotage::with(['produit'])->select('id', 'date_operation', 'produit_id', 'volume_corrige', 'fournisseur')
            ->orderBy('date_operation', 'desc')->limit(10)->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date_operation,
                    'type' => 'Dépotage',
                    'produit' => $item->produit ? $item->produit->nom : 'N/A',
                    'volume' => $item->volume_corrige ?: $item->volume_brut,
                    'societe' => $item->fournisseur ?: 'N/A'
                ];
            });

        $chargements = Chargement::with(['produit'])->select('id', 'date_operation', 'produit_id', 'volume_corrige', 'client_nom')
            ->orderBy('date_operation', 'desc')->limit(10)->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date_operation,
                    'type' => 'Chargement',
                    'produit' => $item->produit ? $item->produit->nom : 'N/A',
                    'volume' => $item->volume_corrige ?: $item->volume_brut,
                    'societe' => $item->client_nom ?: 'N/A'
                ];
            });

        $operations = $depotages->concat($chargements)->sortByDesc('date')->take(5);

        return view('Admin.dashboard', compact('totalUsers', 'activeUsers', 'inactiveUsers', 'stockLevels', 'operations'));
    }

    public function users()
    {
        $this->ensureAdmin();

        $users = User::all();
        $totalUsers = $users->count();
        $activeUsers = $users->where('status', 'active')->count();
        $inactiveUsers = $users->where('status', 'inactive')->count();

        return view('Admin.users', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function addUser()
    {
        $this->ensureAdmin();
        return view('Admin.add-user');
    }

    public function storeUser(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,gestionnaire,marketeur',
            'telephone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Le nom complet est requis.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L’email est requis.',
            'email.email' => 'L’email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est requis.',
            'role.in' => 'Le rôle sélectionné n’est pas valide.',
            'telephone.max' => 'Le numéro de téléphone est trop long.',
            'company_name.max' => 'Le nom de l’entreprise est trop long.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'telephone' => $request->telephone,
            'company_name' => $request->company_name,
            'status' => 'active',
        ]);

        return redirect()->route('admin.users')->with('success', 'Utilisateur ajouté avec succès.');
    }

    public function editUser($id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);
        return view('Admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,gestionnaire,marketeur',
            'telephone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($request->only(['name', 'email', 'role', 'telephone', 'company_name', 'status']));

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function deleteUser($id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);

        if ($this->isProtectedRole($user->role)) {
            if ($user->status !== 'inactive') {
                $user->update(['status' => 'inactive']);
                return redirect()->route('admin.users')->with('success', 'Ce compte est protégé et a été désactivé au lieu d’être supprimé.');
            }

            return redirect()->route('admin.users')->with('info', 'Ce compte protégé est déjà désactivé et ne peut pas être supprimé.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function disableUser($id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);

        if ($user->status === 'inactive') {
            return redirect()->route('admin.users')->with('info', 'Ce compte est déjà désactivé.');
        }

        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users')->with('success', 'Compte désactivé avec succès.');
    }

    public function activateUser($id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);

        if ($user->status === 'active') {
            return redirect()->route('admin.users')->with('info', 'Ce compte est déjà actif.');
        }

        $user->update(['status' => 'active']);

        return redirect()->route('admin.users')->with('success', 'Compte activé avec succès.');
    }

    public function depot()
    {
        $this->ensureAdmin();

        // Récupérer toutes les cuves avec leurs produits
        $cuves = Cuve::with('produit')->get();

        // Récupérer les derniers dépôts (5 derniers)
        $depotages = Depotage::with(['produit', 'cuve'])
            ->orderBy('date_operation', 'desc')
            ->limit(5)
            ->get();

        return view('Admin.depot', compact('cuves', 'depotages'));
    }

    public function transport()
    {
        $this->ensureAdmin();

        // Récupérer les derniers chargements (5 derniers)
        $chargements = Chargement::with(['produit', 'cuve'])
            ->orderBy('date_operation', 'desc')
            ->limit(5)
            ->get();

        return view('Admin.transport', compact('chargements'));
    }

    public function cessions()
    {
        $this->ensureAdmin();

        // Récupérer les dernières cessions (5 dernières)
        $cessions = Cession::with(['produit', 'cuve', 'cedant', 'beneficiaire'])
            ->orderBy('date_cession', 'desc')
            ->limit(5)
            ->get();

        return view('Admin.cessions', compact('cessions'));
    }

    /**
     * Vérifie si le rôle est protégé contre la suppression.
     */
    private function isProtectedRole(string $role): bool
    {
        return in_array($role, ['admin', 'operateur', 'gestionnaire']);
    }
}
