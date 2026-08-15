<?php

return [
    'core' => [
        'app_code' => env('DOSEN_CORE_APP_CODE', 'dosen-farmasi'),
        'connection' => env('DOSEN_CORE_DB_CONNECTION', 'core_mysql'),
        'asset_base_url' => env('DOSEN_CORE_ASSET_BASE_URL', env('CORE_APP_URL')),
        'admin_core_user_ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DOSEN_ADMIN_CORE_USER_IDS', ''))
        ))),
        'testing_all_role_logins' => array_values(array_filter(array_map(
            'strtolower',
            array_map('trim', explode(',', (string) env('DOSEN_TEST_ALL_ROLE_LOGINS', '')))
        ))),
    ],

    'documents' => [
        'disk' => env('DOSEN_PRIVATE_DISK', 'shared_private'),
        'max_kb' => (int) env('DOSEN_DOCUMENT_MAX_KB', 10240),
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'mime_by_extension' => [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/vnd.ms-office'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ],
        'types' => [
            'SURAT_TUGAS',
            'SURAT_KEPUTUSAN',
            'UNDANGAN',
            'BERITA_ACARA',
            'SERTIFIKAT',
            'KONTRAK',
            'LAPORAN',
            'BUKTI_PUBLIKASI',
            'BUKTI_HKI',
            'BAHAN_AJAR',
            'DOKUMENTASI',
            'LAINNYA',
        ],
        'allowed_source_disks' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('DOSEN_ALLOWED_SOURCE_DOCUMENT_DISKS', 'shared_private,dosen_private'))
        ))),
    ],

    'ui_demo' => [
        'preferred_email' => env('DOSEN_UI_DEMO_PREFERRED_EMAIL'),
    ],

    'integration' => [
        'sync_processing' => env('DOSEN_INTEGRATION_SYNC_PROCESSING', true),
        'max_payload_bytes' => (int) env('DOSEN_INTEGRATION_MAX_PAYLOAD_BYTES', 65536),
        'pull_batch_size' => (int) env('DOSEN_INTEGRATION_PULL_BATCH_SIZE', 50),
        'outbox_table' => env('DOSEN_INTEGRATION_OUTBOX_TABLE', 'dosen_integration_outbox'),
        'supported_event_version' => 1,
        'clients' => ['tu-farmasi', 'ta-farmasi', 'kp-farmasi', 'kp-pspa', 'lab-farmasi'],
        'source_connections' => [
            'kp-farmasi' => env('DOSEN_KP_DB_CONNECTION', 'kp_mysql'),
            'kp-pspa' => env('DOSEN_KP_PSPA_DB_CONNECTION', 'kp_pspa_mysql'),
            'lab-farmasi' => env('DOSEN_LAB_DB_CONNECTION', 'lab_mysql'),
        ],
        'abilities' => ['events:push', 'documents:register', 'integration:health'],
        'failure_categories' => [
            'INVALID_PAYLOAD',
            'LECTURER_NOT_FOUND',
            'LECTURER_AMBIGUOUS',
            'UNSUPPORTED_SOURCE_STATE',
            'DOCUMENT_NOT_FOUND',
            'DOCUMENT_CHECKSUM_MISMATCH',
            'STALE_EVENT',
            'DATABASE_SOURCE_UNAVAILABLE',
            'SOURCE_MAPPING_ERROR',
            'HANDLER_EXCEPTION',
        ],
        'mail_enabled' => env('DOSEN_NOTIFICATION_EMAIL_ENABLED', true),
    ],
];
