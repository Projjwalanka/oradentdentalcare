<?php
declare(strict_types=1);

/*
 * Clinic feature copy, shared by the home and Gallery pages.
 *
 * Photos and videos are not listed here: each placeholder reads its own folder
 * under assets/images/ and assets/videos/ (see includes/media_lib.php).
 */

/**
 * What the clinic does about hygiene, equipment and clinical process.
 * Deliberately limited to what is visible in the clinic photographs or is
 * standard, verifiable practice — no equipment claims we cannot stand behind.
 */
const CLINIC_FEATURES = [
    [
        'icon'  => 'sparkle',
        'title' => 'Sterilisation you can watch',
        'text'  => 'Every instrument that goes into a patient\'s mouth is scrubbed, pouched and autoclaved between uses. Needles, blades, gloves, suction tips and cups are single-use and discarded after one patient.',
        'points' => [
            'Autoclaved, pouched instruments opened in front of you',
            'Single-use needles, blades, gloves and suction tips',
            'Chair, light handles and surfaces disinfected between patients',
            'Sharps and clinical waste to authorised bio-medical disposal',
        ],
    ],
    [
        'icon'  => 'tooth',
        'title' => 'Equipment that does the job properly',
        'text'  => 'Treatment happens on a modern dental chair under a dedicated operating light, with a fresh instrument tray set out for each appointment rather than a shared one.',
        'points' => [
            'Modern chair with overhead operating light',
            'Instrument tray prepared per patient',
            'Radiographs taken where diagnosis needs them',
            'Equipment serviced and checked on schedule',
        ],
    ],
    [
        'icon'  => 'shield',
        'title' => 'How treatment is decided',
        'text'  => 'Diagnosis comes before any treatment plan — an examination, and an X-ray where one is needed. Then the options, with their costs, before any work begins.',
        'points' => [
            'Examination and radiograph before a plan is proposed',
            'Costs explained and agreed before treatment starts',
            'Anaesthetic given time to work — nothing rushed',
            'Nothing done without explaining it first',
        ],
    ],
];
