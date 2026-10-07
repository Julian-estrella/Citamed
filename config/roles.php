<?php

return [
    'administrador' => [
        'label' => 'Administrador',
        'permissions' => [
            'gestionar_usuarios',
            'gestionar_pacientes',
            'gestionar_medicos',
            'gestionar_citas',
            'consultar_agenda',
            'visualizar_panel_principal',
        ],
    ],
    'medico' => [
        'label' => 'Médico',
        'permissions' => [
            'consultar_citas',
            'consultar_pacientes_que_atiende',
            'gestionar_horario_atencion',
            'gestionar_disponibilidad',
            'consultar_panel_principal_medico',
            'consultar_historial_citas_pacientes',
        ],
    ],
    'recepcion' => [
        'label' => 'Recepción',
        'permissions' => [
            'registrar_pacientes',
            'consultar_pacientes',
            'modificar_pacientes',
            'gestionar_citas',
            'consultar_agenda',
            'consultar_horarios_disponibles_medicos',
            'programar_citas',
            'modificar_citas',
            'cancelar_citas',
        ],
    ],
];
