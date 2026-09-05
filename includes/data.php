<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Service catalogue and FAQ content.
 *
 * Each service becomes its own indexable page at /services/{slug} with a unique
 * title, meta description, body copy and FAQ block — which is what lets a
 * single-location practice rank for treatment-plus-place queries.
 */

const SERVICES = [

    'root-canal-treatment' => [
        'name'      => 'Root Canal Treatment (RCT)',
        'short'     => 'Pain-free, precision root canal therapy that saves a badly decayed or infected tooth instead of removing it.',
        'icon'      => 'tooth-rct',
        'featured'  => true,
        'duration'  => '45–90 minutes per sitting',
        'sittings'  => 'Usually 1–2 visits',
        'title'     => 'Root Canal Treatment in Sonarpur, Kolkata | Painless RCT — ORADENT',
        'meta'      => 'Painless single-sitting root canal treatment in Sonarpur, Kolkata by Dr. Nabanita Chaklader, BDS (WBUHS), an RCT and oral surgery specialist. Book your RCT consultation online.',
        'lead'      => 'A root canal is the treatment that lets you keep a tooth that would otherwise have to come out. Dr. Nabanita Chaklader has a declared special interest in endodontics, and RCT is the single most requested treatment at ORADENT.',
        'sections'  => [
            [
                'h' => 'When you need a root canal',
                'p' => [
                    'Inside every tooth is a soft core of nerve and blood vessels called the pulp. When deep decay, a crack or an old leaking filling lets bacteria reach that pulp, it becomes inflamed and then infected. The pain is characteristic: it lingers after something hot or cold, it worsens when you lie down at night, and eventually the tooth becomes tender to bite on.',
                    'Left alone, the infection travels out of the root tip into the surrounding bone and forms an abscess. At that point the choice narrows to a root canal or an extraction. Coming in early is what keeps the tooth.',
                    'Some teeth need a root canal without ever hurting much — the nerve has already died quietly. That is why we take an X-ray of any tooth with a deep cavity, a darkened shade, or a history of trauma.',
                ],
            ],
            [
                'h' => 'What happens during treatment',
                'p' => [
                    'The tooth is numbed thoroughly with local anaesthetic — a modern root canal should feel no different from having a filling done. Most patients tell us afterwards that the anticipation was worse than the appointment.',
                    'A small opening is made in the crown of the tooth, the infected pulp is removed, and the root canals are cleaned and shaped along their whole length. The canals are disinfected, dried, and sealed with an inert filling material so bacteria cannot recolonise them. The access opening is then restored.',
                    'Straightforward front teeth are often completed in a single sitting. Molars, badly infected teeth, or teeth with curved canals are usually done across two visits, with a medicated dressing in between to settle the infection.',
                ],
            ],
            [
                'h' => 'The crown afterwards matters',
                'p' => [
                    'A root-treated back tooth has lost its blood supply and a good deal of its structure, which makes it brittle. Placing a crown over it after treatment is not an upsell — it is the difference between a tooth that lasts decades and one that splits within a couple of years.',
                    'We will tell you plainly whether your tooth needs a crown or whether a well-built filling will do, and what each option costs, before any work starts.',
                ],
            ],
            [
                'h' => 'Recovery and aftercare',
                'p' => [
                    'Mild tenderness for two or three days is normal and settles with ordinary painkillers. Avoid chewing hard food on that side until the permanent restoration is fitted.',
                    'Call us if pain increases after the third day, if swelling appears, or if a temporary filling comes out — all three are easy to sort out quickly and difficult to sort out late.',
                ],
            ],
        ],
        'faqs' => [
            ['Is a root canal painful?', 'No. The tooth is fully anaesthetised, so the procedure itself feels like having a filling. What hurts is the infection beforehand — and the root canal is what stops it.'],
            ['Can it be done in one sitting?', 'Often, yes, particularly for front teeth and teeth without active infection. Molars and abscessed teeth are usually safer over two visits.'],
            ['Is it better to just extract the tooth?', 'Almost never, if the tooth is restorable. Nothing replaces a natural tooth as well as the natural tooth. An extraction also starts bone loss and lets neighbouring teeth drift.'],
            ['How long does a root-treated tooth last?', 'Properly treated and properly crowned, it can last a lifetime. The two things that shorten it are skipping the crown and untreated gum disease.'],
        ],
    ],

    'dental-implants' => [
        'name'      => 'Dental Implants',
        'short'     => 'Permanent, natural-looking replacements for missing teeth that restore full bite strength without touching the neighbouring teeth.',
        'icon'      => 'implant',
        'featured'  => true,
        'duration'  => '60–90 minutes for placement',
        'sittings'  => 'Placement, then crown after healing',
        'title'     => 'Dental Implants in Sonarpur, Kolkata | Single & Multiple Tooth — ORADENT',
        'meta'      => 'Dental implants in Sonarpur, Kolkata. Titanium implants placed by an oral and maxillofacial surgery specialist to replace single or multiple missing teeth. Book a consultation.',
        'lead'      => 'An implant is a small titanium post placed into the jawbone to take the place of a missing tooth root. Because Dr. Chaklader\'s second special interest is oral and maxillofacial surgery, implant placement and the surgical planning behind it are done in-house.',
        'sections'  => [
            [
                'h' => 'Why replace a missing tooth at all',
                'p' => [
                    'A gap is not just cosmetic. The teeth on either side tilt into it, the opposing tooth over-erupts, and chewing load shifts onto teeth that were never designed to carry it. Meanwhile the bone that used to hold the root begins to resorb, which is why long-standing gaps change the shape of the face.',
                    'An implant is the only replacement that loads the bone the way a real root does, which is what stops that bone loss.',
                ],
            ],
            [
                'h' => 'Implant, bridge or denture?',
                'p' => [
                    'A bridge is quicker and cheaper but requires cutting down the healthy teeth on either side of the gap. A removable partial denture is cheapest but least comfortable and does nothing for the bone.',
                    'An implant costs more at the outset and takes longer, but it stands alone, it does not damage its neighbours, and with good hygiene it is the option most likely to still be in place in twenty years. We will lay out all three honestly for your particular gap.',
                ],
            ],
            [
                'h' => 'The treatment sequence',
                'p' => [
                    'We start with an examination and radiographs to measure the height and width of available bone and to locate the nerve and sinus. If bone volume is short, a graft may be planned at the same appointment as placement.',
                    'Placement itself is done under local anaesthetic and is usually more comfortable than an extraction. The implant is then left to integrate with the bone — typically three to four months in the lower jaw and four to six in the upper.',
                    'Once integration is confirmed, an abutment and a custom-made crown are fitted. The shade is matched to your existing teeth so the finished tooth is not identifiable as an implant.',
                ],
            ],
            [
                'h' => 'Who is a good candidate',
                'p' => [
                    'Most healthy adults with finished jaw growth. Controlled diabetes is not a barrier; uncontrolled diabetes and active smoking both meaningfully raise the failure rate, and we will be direct with you about that before you commit.',
                    'Active gum disease must be treated first — an implant placed into an unhealthy mouth is an implant that will be lost.',
                ],
            ],
        ],
        'faqs' => [
            ['Is implant surgery painful?', 'It is done under local anaesthetic and most patients report less discomfort than a difficult extraction. Mild soreness for a few days afterwards is normal.'],
            ['How long does the whole process take?', 'Typically three to six months from placement to final crown, because the bone needs time to fuse to the implant. The waiting is what makes it last.'],
            ['How long do implants last?', 'The titanium post is designed to be permanent. The crown on top may need replacing after ten to fifteen years of normal wear.'],
            ['Do I need a bone graft?', 'Only if the bone at the site has already resorbed. We can tell you after the X-ray at your first consultation.'],
        ],
    ],

    'oral-maxillofacial-surgery' => [
        'name'      => 'Oral & Maxillofacial Surgery',
        'short'     => 'Advanced surgical treatment for impacted wisdom teeth, cysts, jaw conditions and complex oral pathology.',
        'icon'      => 'surgery',
        'featured'  => true,
        'duration'  => 'Varies by procedure',
        'sittings'  => 'Consultation, then planned surgery',
        'title'     => 'Oral & Maxillofacial Surgery in Sonarpur, Kolkata — ORADENT Dental Care',
        'meta'      => 'Oral and maxillofacial surgery in Sonarpur, Kolkata — impacted wisdom tooth removal, minor oral surgery, cysts and biopsies, by Dr. Nabanita Chaklader, BDS (WBUHS).',
        'lead'      => 'Oral and maxillofacial surgery covers the conditions of the mouth and jaws that go beyond routine dentistry. It is one of Dr. Chaklader\'s two declared special interests, which means these cases are managed here rather than referred on.',
        'sections'  => [
            [
                'h' => 'Impacted wisdom teeth',
                'p' => [
                    'Wisdom teeth arrive last and often find no room. A partially erupted lower wisdom tooth traps plaque under its gum flap and causes repeated episodes of pain, swelling and difficulty opening the mouth — pericoronitis. A horizontally impacted one can also decay the molar in front of it, quietly destroying a healthy tooth.',
                    'Surgical removal is planned from a radiograph that shows the root shape and the position of the inferior alveolar nerve. The tooth is sectioned and removed through a small incision, and the site is sutured.',
                    'Not every wisdom tooth needs to come out. If yours is fully erupted, cleanable and causing no trouble, we will tell you to keep it.',
                ],
            ],
            [
                'h' => 'Other procedures we handle',
                'p' => [
                    'Surgical removal of retained roots and broken-down teeth; removal of small cysts and benign lesions of the jaws; biopsy of suspicious soft-tissue lesions; apicoectomy where a root canal alone has not resolved an infection; and pre-prosthetic surgery to prepare the ridge for dentures or implants.',
                    'Frenectomy for a restrictive tongue-tie or lip-tie, in both children and adults, is also done here.',
                ],
            ],
            [
                'h' => 'Safety and referral',
                'p' => [
                    'Every surgical case begins with a medical history — anticoagulants, bisphosphonates, uncontrolled diabetes and a history of head and neck radiotherapy all change how a case is managed.',
                    'Where a condition needs hospital facilities, general anaesthesia or a multidisciplinary team, we say so and refer promptly with full records. Knowing the limit of what should be done in a clinic is part of doing this work properly.',
                ],
            ],
        ],
        'faqs' => [
            ['How long does wisdom tooth surgery take?', 'Most take twenty to forty minutes. The planning beforehand takes longer than the procedure.'],
            ['How much swelling should I expect?', 'Swelling peaks around 48 hours and settles over the following few days. Ice for the first day, warm salt-water rinses from the second.'],
            ['Will my jaw be numb afterwards?', 'The anaesthetic wears off in a few hours. We assess nerve proximity on the X-ray beforehand precisely to avoid lasting numbness.'],
            ['Do I need someone to come with me?', 'For a straightforward extraction under local anaesthetic, no. For longer surgical cases we recommend it.'],
        ],
    ],

    'tooth-extraction' => [
        'name'      => 'Tooth Extraction',
        'short'     => 'Routine and surgical extractions carried out gently, with clear aftercare and a plan for replacing the tooth.',
        'icon'      => 'extraction',
        'featured'  => false,
        'duration'  => '20–40 minutes',
        'sittings'  => 'Single visit',
        'title'     => 'Tooth Extraction in Sonarpur, Kolkata | Painless Removal — ORADENT',
        'meta'      => 'Gentle, painless tooth extraction in Sonarpur, Kolkata. Simple and surgical removals with clear aftercare, by Dr. Nabanita Chaklader, BDS (WBUHS). Book online.',
        'lead'      => 'Removing a tooth is the last resort, not the first. When a tooth genuinely cannot be saved, our aim is that the procedure is calm, quick and comfortable — and that you leave knowing what happens next.',
        'sections'  => [
            [
                'h' => 'When extraction is the right call',
                'p' => [
                    'A tooth is taken out when it is broken below the gum, split vertically, decayed beyond restoration, loose from advanced gum disease, or impacted and causing recurring infection. Orthodontic treatment occasionally needs a healthy premolar removed to create space.',
                    'If a root canal, a crown or a filling can save it, we will offer that first and explain the cost difference honestly.',
                ],
            ],
            [
                'h' => 'What it feels like',
                'p' => [
                    'The area is numbed completely. You will feel firm pressure and hear some movement, but not pain — pressure and pain travel along different nerve fibres, and only the pain fibres are blocked.',
                    'Simple extractions take minutes. Surgical extractions, where the tooth is sectioned or a small flap is raised, take longer and are closed with sutures.',
                ],
            ],
            [
                'h' => 'Aftercare that prevents a dry socket',
                'p' => [
                    'Bite firmly on the gauze for thirty minutes. For the next 24 hours: no rinsing, no spitting, no straws, no smoking, and nothing hot. Those instructions exist to protect the blood clot in the socket — losing it causes a dry socket, which is genuinely painful and entirely avoidable.',
                    'From the second day, gentle warm salt-water rinses help. Eat on the other side and keep the rest of your mouth clean as usual.',
                ],
            ],
            [
                'h' => 'Replacing the gap',
                'p' => [
                    'Except for wisdom teeth, most extracted teeth should be replaced. We will discuss implants, a bridge or a denture at the time, so the decision is made deliberately rather than by default years later once the bone has shrunk.',
                ],
            ],
        ],
        'faqs' => [
            ['How long does bleeding last?', 'Oozing for a few hours is normal. Steady bleeding beyond that means the clot has been disturbed — bite on fresh gauze and call us.'],
            ['When can I eat?', 'Once the numbness has gone, so you do not bite your lip or cheek. Start with soft, cool food.'],
            ['Can I go to work the next day?', 'After a simple extraction, usually yes. After surgical removal, plan a quiet day.'],
            ['How soon can the gap be filled?', 'Bone needs about three months to heal before an implant. A temporary replacement can be made much sooner.'],
        ],
    ],

    'scaling-and-polishing' => [
        'name'      => 'Scaling & Polishing',
        'short'     => 'Ultrasonic cleaning that removes hardened tartar and stain, treats bleeding gums, and leaves your mouth genuinely fresh.',
        'icon'      => 'clean',
        'featured'  => true,
        'duration'  => '30–45 minutes',
        'sittings'  => 'Single visit, repeated every 6–12 months',
        'title'     => 'Teeth Cleaning, Scaling & Polishing in Sonarpur, Kolkata — ORADENT',
        'meta'      => 'Professional scaling and polishing in Sonarpur, Kolkata. Ultrasonic teeth cleaning for bleeding gums, tartar and stains, from ₹ affordable rates. Book your cleaning online.',
        'lead'      => 'Scaling is the single most cost-effective dental treatment there is. It takes under an hour, it is not painful, and it is what prevents the gum disease that causes most adult tooth loss.',
        'sections'  => [
            [
                'h' => 'Why brushing is not enough',
                'p' => [
                    'Plaque that is not removed within about 48 hours mineralises into calculus — tartar — which is hard, porous and bonded to the tooth. No toothbrush removes it. It sits at and below the gumline holding bacteria against the tissue.',
                    'The gums respond with inflammation: they bleed when brushed, they look puffy, and breath turns stale. That stage, gingivitis, is completely reversible. If it is left, the inflammation spreads to the bone holding the teeth, and that damage is not reversible.',
                ],
            ],
            [
                'h' => 'What the appointment involves',
                'p' => [
                    'An ultrasonic scaler vibrates the tartar loose with a fine water spray, and hand instruments clean the areas the tip cannot reach, including just below the gumline. The teeth are then polished to remove surface stain from tea, coffee, tobacco and paan, and to leave a smooth surface that slows the return of plaque.',
                    'You will hear a high-pitched buzz and feel cold water. If your gums are inflamed there may be some tenderness and bleeding during the clean — that is the inflammation, not the instrument, and it settles within days.',
                ],
            ],
            [
                'h' => 'The myth about scaling loosening teeth',
                'p' => [
                    'This one keeps people away from the dentist for years, so it is worth stating plainly: scaling does not loosen teeth and does not thin enamel. Where tartar has been present for a long time it is partly splinting already-loose teeth and filling spaces left by receded gums. Removing it reveals the damage that was hidden — it does not cause it.',
                    'Sensitivity for a week or two afterwards is common and passes. A desensitising toothpaste helps in the meantime.',
                ],
            ],
        ],
        'faqs' => [
            ['How often should I have my teeth cleaned?', 'Every six months for most people; every three to four months if you have gum disease, diabetes, or smoke.'],
            ['Does scaling hurt?', 'Not usually. Healthy gums feel only vibration and cold water. Inflamed gums can be tender, which is itself a sign the clean is needed.'],
            ['Will it whiten my teeth?', 'It removes surface staining, so teeth look noticeably brighter — but it does not change the natural shade of the enamel. That needs whitening.'],
            ['My gums bleed when I brush. Should I stop brushing?', 'No — the opposite. Bleeding means inflammation from plaque. Keep brushing gently and come in for a clean.'],
        ],
    ],

    'tooth-filling' => [
        'name'      => 'Tooth Filling',
        'short'     => 'Tooth-coloured composite fillings that repair decay and chips invisibly and restore normal chewing.',
        'icon'      => 'filling',
        'featured'  => false,
        'duration'  => '30–45 minutes',
        'sittings'  => 'Single visit',
        'title'     => 'Tooth Filling in Sonarpur, Kolkata | Tooth-Coloured Composite — ORADENT',
        'meta'      => 'Tooth-coloured composite fillings in Sonarpur, Kolkata. Same-day cavity treatment that restores strength and appearance. Book with Dr. Nabanita Chaklader online.',
        'lead'      => 'A filling repairs a tooth before the decay reaches the nerve. Catching a cavity at this stage is the difference between a thirty-minute appointment and a root canal.',
        'sections'  => [
            [
                'h' => 'Spotting decay early',
                'p' => [
                    'Early decay causes no pain at all. By the time a tooth is sensitive to sweet or cold, the cavity has usually reached the dentine underneath the enamel. By the time it aches on its own, the nerve is involved.',
                    'That gap between "no symptoms" and "needs a root canal" is exactly what a routine six-monthly check-up is for. Small cavities between teeth are found on bitewing X-rays long before they can be seen or felt.',
                ],
            ],
            [
                'h' => 'Composite, not amalgam',
                'p' => [
                    'We place tooth-coloured composite resin, shade-matched to the surrounding tooth. It bonds chemically to the tooth structure, which means less healthy tooth has to be cut away to hold it in place than with traditional silver amalgam.',
                    'The result is invisible in normal conversation, and it seals the margin against further decay. For very large cavities where a filling would be unreliable, we will recommend an inlay or a crown instead of building up a filling that is likely to fracture.',
                ],
            ],
            [
                'h' => 'How long fillings last',
                'p' => [
                    'A well-placed composite in a well-maintained mouth lasts many years. Fillings fail at their margins, where new decay creeps in, so the things that extend their life are the boring ones: flossing, cutting down on frequent sugar, and having the margins checked at each visit.',
                ],
            ],
        ],
        'faqs' => [
            ['Will I need an injection?', 'For a shallow filling, often not. For anything reaching the dentine, local anaesthetic makes it comfortable.'],
            ['Can I eat straight afterwards?', 'Yes — composite is set hard with a curing light before you leave. Just wait for numbness to pass.'],
            ['Why is my filled tooth sensitive to cold?', 'Mild sensitivity for a week or two after a deep filling is normal. If it persists or worsens, come back and let us check the bite.'],
            ['Do you remove old silver fillings?', 'Only when they are failing, leaking or fractured — not routinely, since replacing a sound filling means removing more tooth.'],
        ],
    ],

    'cosmetic-dentistry' => [
        'name'      => 'Cosmetic Dentistry',
        'short'     => 'Whitening, veneers, edge reshaping and smile makeovers planned around your face, not a template.',
        'icon'      => 'smile',
        'featured'  => false,
        'duration'  => '45–90 minutes per visit',
        'sittings'  => 'Depends on the plan',
        'title'     => 'Cosmetic Dentistry & Smile Makeover in Sonarpur, Kolkata — ORADENT',
        'meta'      => 'Teeth whitening, veneers and smile makeovers in Sonarpur, Kolkata. Natural-looking cosmetic dentistry planned around your face by Dr. Nabanita Chaklader.',
        'lead'      => 'Good cosmetic dentistry is the kind nobody notices. The aim is teeth that suit your face and look like they grew there — not a uniform white row that announces itself.',
        'sections'  => [
            [
                'h' => 'Teeth whitening',
                'p' => [
                    'Whitening lifts the natural shade of the enamel using a peroxide gel. In-clinic whitening works in a single appointment under supervision; a custom take-home tray kit works more gradually over one to two weeks and tends to give a more stable result.',
                    'It is worth knowing what whitening cannot do: it does not change the colour of crowns, veneers or fillings. If you have visible restorations on your front teeth, they will need replacing afterwards to match — which is why we plan the sequence before starting.',
                ],
            ],
            [
                'h' => 'Veneers and bonding',
                'p' => [
                    'Composite bonding reshapes a chipped edge, closes a small gap or masks a discoloured tooth in one appointment, with little or no drilling. It is reversible, affordable, and repairable.',
                    'Ceramic veneers are thin shells cemented over the front of the teeth. They are the stronger, more stain-resistant option for larger changes, but they require some enamel to be removed and are a permanent commitment. We are careful to make sure patients understand that distinction before choosing.',
                ],
            ],
            [
                'h' => 'Planning a smile makeover',
                'p' => [
                    'A makeover starts with what bothers you, in your words. We then look at tooth proportion, the position of the midline, how much gum shows when you smile, and how the edges of the upper teeth follow the lower lip.',
                    'Health comes before appearance. Untreated decay and gum disease are dealt with first — cosmetic work built over an unhealthy foundation does not last.',
                ],
            ],
        ],
        'faqs' => [
            ['Does whitening damage enamel?', 'Professionally supervised whitening at correct concentrations does not. Temporary sensitivity is the usual side effect and settles.'],
            ['How long does whitening last?', 'Typically one to two years, less if you drink a lot of tea, coffee or red wine, or use tobacco.'],
            ['Are veneers reversible?', 'Ceramic veneers are not, because enamel is removed. Composite bonding largely is. We will always tell you which category a proposed treatment falls into.'],
            ['Will my new teeth look fake?', 'Not if they are planned properly. We match shade, translucency and shape to your face and age.'],
        ],
    ],

    'pediatric-dental-care' => [
        'name'      => 'Pediatric Dental Care',
        'short'     => 'Gentle, unhurried dental care for children — with a first visit designed so that dentistry never becomes something to fear.',
        'icon'      => 'child',
        'featured'  => true,
        'duration'  => '20–40 minutes',
        'sittings'  => 'Six-monthly reviews',
        'title'     => 'Children\'s Dentist in Sonarpur, Kolkata | Pediatric Dental Care — ORADENT',
        'meta'      => 'Friendly pediatric dental care in Sonarpur, Kolkata — check-ups, fluoride, sealants and milk-tooth treatment for children of all ages. Book your child\'s visit online.',
        'lead'      => 'A child\'s first few dental visits set their attitude to dentistry for life. We keep them short, unhurried and honest, and we never spring a surprise on a child.',
        'sections'  => [
            [
                'h' => 'When should a child first see a dentist?',
                'p' => [
                    'By the first birthday, or within six months of the first tooth appearing. That first visit is not about treatment — it is a look inside the mouth, a word about brushing and bottle habits, and a chance for your child to sit in the chair while nothing happens.',
                    'Children who meet the dentist before they have a problem are dramatically easier to treat when they do.',
                ],
            ],
            [
                'h' => 'Why milk teeth matter',
                'p' => [
                    '"They are going to fall out anyway" is the most costly misconception in children\'s dentistry. Milk teeth hold the space for the permanent teeth behind them; losing one early lets the neighbours drift and crowds the adult tooth out of position.',
                    'Decay in a milk tooth also causes real pain, disturbed sleep and missed school, and infection at the root can damage the developing permanent tooth underneath it.',
                ],
            ],
            [
                'h' => 'Prevention we offer',
                'p' => [
                    'Fluoride varnish applications, which measurably reduce decay in children. Fissure sealants — a thin protective coating flowed into the deep grooves of newly erupted permanent molars, painlessly and without drilling. Diet advice focused on how often sugar is eaten rather than how much.',
                    'Where treatment is needed, we explain it to the child in their own words first, using tell-show-do. No child is held down and nothing is done that they have not been shown.',
                ],
            ],
        ],
        'faqs' => [
            ['My child is terrified. What can we do?', 'Book a visit with no treatment planned. Meeting the clinic without anything happening is often all it takes to break the fear.'],
            ['Should I stay in the room?', 'For younger children, yes. For anxious older ones, children sometimes cope better without an audience — we will judge it together.'],
            ['When do adult teeth come through?', 'The first permanent molars and lower front teeth appear around age six. The rest follow through to about age twelve.'],
            ['Is thumb-sucking a problem?', 'Not before four or five. Beyond that it can affect the position of the front teeth and the shape of the palate, and is worth addressing.'],
        ],
    ],
];

/** Homepage / general FAQs — these feed the FAQPage schema on the FAQ page. */
const GENERAL_FAQS = [
    ['Where exactly is ORADENT Dental Care?',
     'We are at Alap Apartment, 105 Maharani Indira Devy Road, near the Rabindra Nagar Auto Stand — close to Sakshi Baban and opposite Gurunan Math, Sonarpur, Kolkata 700103. There is an auto stand directly outside, and the clinic is on the ground floor.'],

    ['What are your opening hours?',
     'Monday to Saturday, 10:00 AM to 1:00 PM in the morning and 5:00 PM to 9:00 PM in the evening. We are closed on Sundays. You can book any available slot online up to 30 days in advance.'],

    ['Do I need an appointment, or can I walk in?',
     'Walk-ins are welcome but you may have to wait. Booking online takes under a minute, confirms your slot immediately, and means you are seen at the time you chose.'],

    ['How do I book an appointment?',
     'Use the booking form on this site — pick a date, pick a free time slot, and enter your name and mobile number. Your slot is confirmed on the spot and you get a reference number. You can also call or WhatsApp us on ' . CLINIC_PHONE_DISPLAY . '.'],

    ['Can I change or cancel my appointment?',
     'Yes. Call or WhatsApp us on ' . CLINIC_PHONE_DISPLAY . ' with your reference number and we will move or cancel it. Please give us as much notice as you can so the slot can go to someone else.'],

    ['Who will treat me?',
     'Dr. Nabanita Chaklader, BDS (WBUHS), a dental surgeon with declared special interests in root canal treatment and in oral and maxillofacial surgery.'],

    ['Is the clinic suitable for children and elderly patients?',
     'Yes. We treat all ages, and appointments for children and for elderly patients are given extra time so nothing is rushed.'],

    ['What about sterilisation and infection control?',
     'Instruments are cleaned, packed and autoclaved between every patient. Single-use items are used once and discarded, and surfaces are disinfected between appointments.'],

    ['Do you handle dental emergencies?',
     'Yes. For severe pain, swelling, a knocked-out tooth or bleeding after an extraction, call us on ' . CLINIC_PHONE_DISPLAY . ' rather than booking online, and we will fit you in.'],

    ['What payment methods do you accept?',
     'Cash and UPI. We will give you a clear estimate of the cost before starting any treatment, so there are no surprises.'],
];

/** Why-choose-us pillars, reused on the home and about pages. */
const PILLARS = [
    ['icon' => 'shield',   'title' => 'Specialist-led care',
     'text' => 'Treated by a dental surgeon with declared special interests in RCT and oral & maxillofacial surgery — complex cases are handled here, not referred away.'],
    ['icon' => 'sparkle',  'title' => 'Strict sterilisation',
     'text' => 'Autoclaved instruments, single-use disposables and surface disinfection between every single patient, without exception.'],
    ['icon' => 'clock',    'title' => 'Appointments that run on time',
     'text' => 'Slots are 15 minutes and bookable online. You are seen close to the time you chose, not an hour after it.'],
    ['icon' => 'rupee',    'title' => 'Costs explained upfront',
     'text' => 'You get a clear estimate before treatment starts, and an honest view of which options are worth the money for your case.'],
    ['icon' => 'heart',    'title' => 'Gentle with anxious patients',
     'text' => 'Nervous patients and children are given extra time. Nothing is done without explaining it first.'],
    ['icon' => 'family',   'title' => 'Every age, one clinic',
     'text' => 'From a toddler\'s first check-up to dentures and implants for grandparents — the whole family under one roof.'],
];

function service(string $slug): ?array
{
    return SERVICES[$slug] ?? null;
}

function featured_services(): array
{
    return array_filter(SERVICES, static fn(array $s) => $s['featured']);
}
