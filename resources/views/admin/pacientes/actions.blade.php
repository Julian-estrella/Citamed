<td class="px-4 py-3">
    <div class="flex items-center justify-end gap-2">
        @php
            $showRoute = $user->isAdmin() ? 'admin.patients.show' : ($user->isMedico() ? 'medico.patients.show' : 'recepcion.patients.show');
            $editRoute = $user->isAdmin() ? 'admin.patients.edit' : 'recepcion.patients.edit';
        @endphp

        <a href="{{ route($showRoute, $patient) }}" title="Ver expediente" aria-label="Ver expediente de {{ $patient->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
            <i class="fa-solid fa-eye"></i>
        </a>

        @if ($user->hasPermission('modificar_pacientes') || $user->hasPermission('gestionar_pacientes'))
            <a href="{{ route($editRoute, $patient) }}" title="Editar paciente" aria-label="Editar paciente {{ $patient->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#223FAA] hover:bg-slate-50">
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        @endif

        @if ($canTogglePatients)
            <form action="{{ route($toggleRoute, $patient) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" title="{{ $patient->is_active ? 'Desactivar paciente' : 'Activar paciente' }}" aria-label="{{ $patient->is_active ? 'Desactivar' : 'Activar' }} paciente {{ $patient->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid {{ $patient->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
            </form>
        @endif

        @if ($canDeletePatients)
            <form action="{{ route('admin.patients.destroy', $patient) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar a {{ addslashes($patient->name) }}? Si tiene citas registradas, no se podrá eliminar.');">
                @csrf
                @method('DELETE')
                <button type="submit" title="Eliminar paciente" aria-label="Eliminar paciente {{ $patient->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
</td>