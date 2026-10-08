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
        'panels' => [
            [
                'key' => 'admin.panel',
                'label' => 'Panel administrativo',
                'route' => 'admin.panel',
                'permission' => 'visualizar_panel_principal',
            ],
            [
                'key' => 'admin.users',
                'label' => 'Usuarios',
                'route' => 'admin.users',
                'permission' => 'gestionar_usuarios',
            ],
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
        'panels' => [
            [
                'key' => 'medico.dashboard',
                'label' => 'Panel médico',
                'route' => 'medico.dashboard',
                'permission' => 'consultar_panel_principal_medico',
            ],
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
        'panels' => [
            [
                'key' => 'recepcion.dashboard',
                'label' => 'Panel de recepción',
                'route' => 'recepcion.dashboard',
                'permission' => 'consultar_agenda',
            ],
        ],
    ],
];
