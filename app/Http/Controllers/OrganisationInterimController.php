<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationInterim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestion des intérims : désigner un intérimaire pour le responsable d'un service
 * sur une période donnée. Tant qu'un intérim est actif, le courrier adressé au
 * service est routé vers l'intérimaire (voir Organisation::responsible()).
 */
class OrganisationInterimController extends Controller
{
    /**
     * Seuls le DG et les superadmins gèrent les intérims.
     */
    private function requireManager(): void
    {
        $user = Auth::user();

        abort_unless(
            $user->isSuperAdmin() || $user->hasRoleInOrganisation('DG', $user->current_organisation_id),
            403,
            'Seul le Directeur Général peut gérer les intérims.'
        );
    }

    public function index()
    {
        $this->requireManager();

        $interims = OrganisationInterim::with(['organisation', 'titular', 'interim'])
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->paginate(20);

        return view('organisations.interims.index', compact('interims'));
    }

    public function create()
    {
        $this->requireManager();

        $organisations = Organisation::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('organisations.interims.create', compact('organisations', 'users'));
    }

    public function store(Request $request)
    {
        $this->requireManager();

        $data = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'titular_user_id' => 'required|exists:users,id',
            'interim_user_id' => 'required|exists:users,id|different:titular_user_id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ], [
            'interim_user_id.different' => "L'intérimaire doit être différent du titulaire.",
        ]);

        OrganisationInterim::create($data + [
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('organisation-interims.index')
            ->with('success', 'Intérim enregistré avec succès.');
    }

    /**
     * Clôture un intérim (désactivation immédiate).
     */
    public function deactivate(OrganisationInterim $interim)
    {
        $this->requireManager();

        $interim->update(['is_active' => false]);

        return back()->with('success', 'Intérim clôturé.');
    }

    public function destroy(OrganisationInterim $interim)
    {
        $this->requireManager();

        $interim->delete();

        return back()->with('success', 'Intérim supprimé.');
    }
}
