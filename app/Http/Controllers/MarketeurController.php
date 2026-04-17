<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cession;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Marketeur;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des fonctionnalités du marketeur
 */
class MarketeurController extends Controller
{
    /**
     * Constructeur : applique les middlewares d'authentification et de rôle
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:marketeur');
    }

    /**
     * Affiche le dashboard du marketeur avec statistiques et cessions récentes
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Récupération du marketeur associé à l'utilisateur connecté
        $marketeur = Auth::user()->marketeur;

        // Comptage des cessions envoyées et reçues
        $cessionsEnvoyees = Cession::where('cedant_id', $marketeur?->id ?? 0)->count();
        $cessionsRecues = Cession::where('beneficiaire_id', $marketeur?->id ?? 0)->count();

        // Calcul des volumes totaux cédés et reçus
        $totalVolumeCede = Cession::where('cedant_id', $marketeur?->id ?? 0)->sum('volume');
        $totalVolumeRecu = Cession::where('beneficiaire_id', $marketeur?->id ?? 0)->sum('volume');

        // Récupération des 10 cessions les plus récentes
        $recentCessions = Cession::where(function($q) use ($marketeur) {
            $q->where('cedant_id', $marketeur?->id ?? 0)
              ->orWhere('beneficiaire_id', $marketeur?->id ?? 0);
        })->with(['cedant', 'beneficiaire', 'produit'])->latest()->take(10)->get();

        // Retour de la vue avec les données
        return view('marketeur.dashboard', compact(
            'cessionsEnvoyees', 'cessionsRecues', 'totalVolumeCede', 'totalVolumeRecu', 'recentCessions'
        ));
    }

    /**
     * Affiche la liste des opérations (dépotages et chargements) du marketeur
     * @return \Illuminate\View\View
     */
    public function operations()
    {
        // Récupération du marketeur
        $marketeur = Auth::user()->marketeur;

        // Récupération des depotages liés au marketeur
        $depotages = Depotage::where('fournisseur', $marketeur?->company_name ?? '')
            ->orWhere('client_nom', $marketeur?->company_name ?? '')
            ->with(['produit', 'cuve'])
            ->latest()
            ->paginate(10);

        // Récupération des chargements liés au marketeur
        $chargements = Chargement::where('client_nom', $marketeur?->company_name ?? '')
            ->with(['produit', 'cuve'])
            ->latest()
            ->paginate(10);

        // Retour de la vue avec les données
        return view('marketeur.operations', compact('depotages', 'chargements'));
    }

    /**
     * Affiche la liste des cessions du marketeur
     * @return \Illuminate\View\View
     */
    public function cessions()
    {
        // Récupération du marketeur
        $marketeur = Auth::user()->marketeur;

        // Cessions envoyées par le marketeur
        $cessionsEnvoyees = Cession::where('cedant_id', $marketeur?->id ?? 0)
            ->with(['beneficiaire', 'produit', 'cuve'])
            ->latest()
            ->paginate(15);

        // Cessions reçues par le marketeur
        $cessionsRecues = Cession::where('beneficiaire_id', $marketeur?->id ?? 0)
            ->with(['cedant', 'produit', 'cuve'])
            ->latest()
            ->paginate(15);

        // Retour de la vue
        return view('marketeur.cessions', compact('cessionsEnvoyees', 'cessionsRecues'));
    }

    /**
     * Affiche les détails d'une cession spécifique
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showCession($id)
    {
        // Récupération du marketeur et de la cession
        $marketeur = Auth::user()->marketeur;
        $cession = Cession::with(['cedant', 'beneficiaire', 'produit', 'cuve'])->findOrFail($id);

        // Vérification des droits d'accès
        if ($cession->cedant_id != $marketeur?->id && $cession->beneficiaire_id != $marketeur?->id) {
            abort(403);
        }

        // Retour de la vue avec la cession
        return view('marketeur.cession-detail', compact('cession'));
    }
}
