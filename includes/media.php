<?php
declare(strict_types=1);

/**
 * Clinic feature copy, and the (currently unused) video manifest.
 *
 * SLIDER IMAGES NO LONGER LIVE HERE. They are read straight from
 *   assets/img/slider/
 * by includes/slider.php — drop a file in that folder and it appears; delete it
 * and it goes. See assets/img/slider/README.txt.
 *
 * CLINIC_MEDIA below is kept only to hold the video entries until those are
 * cleared for publication.
 */

const CLINIC_MEDIA = [

    /*
     * ---------------------------------------------------------------------
     * CLINIC TOUR VIDEOS — built and ready, but NOT published.
     *
     * Both clips (and Image-5.jpeg) show patients undergoing treatment with
     * identifiable faces. Publishing those needs each patient's explicit,
     * recorded consent. Once you have it, delete this comment block's markers
     * and the two entries below go live.
     *
     * The files already exist: assets/video/clinic-tour-1.mp4 and -2.mp4
     * (20s, muted, ~1 MB each, with poster frames).
     * ---------------------------------------------------------------------
     *
    [
        'type'    => 'video',
        'file'    => 'clinic-tour-1',
        'video'   => 'clinic-tour-1.mp4',
        'alt'     => 'Video showing a patient being treated in the chair at ORADENT Dental Care',
        'title'   => 'A visit, start to finish',
        'caption' => 'What an appointment actually looks like.',
    ],
    [
        'type'    => 'video',
        'file'    => 'clinic-tour-2',
        'video'   => 'clinic-tour-2.mp4',
        'alt'     => 'Video showing treatment being carried out at ORADENT Dental Care',
        'title'   => 'In the chair',
        'caption' => 'Chairside assistance throughout the procedure.',
    ],
     */
];

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
