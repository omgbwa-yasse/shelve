{{--
    Activité du plan de classement du courrier.

    Ce champ commande le classement, la durée de conservation ET le cloisonnement
    des intérims : un volet d'intérim porte une activité, et un courrier qui n'en a
    aucune n'est traitable par aucun intérimaire à volet. Il doit donc être saisi
    dès la création, sans attendre la cotation.

    Paramètres : $activities (collection), $mail (nullable).
--}}
<div class="col-md-4 mb-3">
    <label for="activity_id" class="form-label">Activité (plan de classement)</label>
    <select name="activity_id" id="activity_id" class="form-select">
        <option value="">Non rattaché…</option>
        @foreach($activities as $activity)
            <option value="{{ $activity->id }}"
                {{ (int) old('activity_id', $mail->activity_id ?? null) === (int) $activity->id ? 'selected' : '' }}>
                {{ $activity->name }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        Rattache le courrier au plan de classement. Nécessaire pour que les
        <strong>intérims par volet</strong> puissent le router.
    </small>
</div>
