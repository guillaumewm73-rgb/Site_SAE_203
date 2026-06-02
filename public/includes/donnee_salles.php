<?php

declare(strict_types=1);

function getRoomCatalog(): array
{
    $sourceCatalog = [
        '001' => [
            'number' => '001',
            'title' => 'Miroirs numériques',
            'slug' => 'salle-001.php',
            'heroImage' => 'assets/images/carousel-expo-1.jpg',
            'supportLabel' => 'TP 1.1 - Community / Distorsion',
            'summary' => 'Deux œuvres autour de l’image altérée, du regard collectif et de ce qui nous échappe quand notre visage devient une matière à manipuler.',
            'question' => 'Comment une image devient-elle une matière collective ?',
            'focus' => 'Identité, image et regard collectif',
            'concept' => <<<'TEXT'
L'oeuvre montre que, dans l'univers numerique, notre visage devient une matiere que les autres peuvent modifier, detourner ou reinventer. Cette transformation imposee cree une version de nous qui echappe a notre controle.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Community',
                    'description' => <<<'TEXT'
Titre present dans l'ODS, sans description associee.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'Distorsion',
                    'description' => <<<'TEXT'
L'oeuvre Distorsion explore l'emancipation de notre identite dans un recit ou notre image ne nous appartient plus. Elle montre que, dans l'univers numerique, notre visage devient une matiere que les autres peuvent modifier, detourner ou reinventer. Cette transformation imposee cree une version de nous qui echappe a notre controle. Dans Distorsion, cette image alteree est ensuite mise en vente, comme un produit parmi d'autres, revelant comment la societe de consommation s'approprie jusqu'a notre identite. Le visage devient un objet marchand, faconne par le regard collectif, que chacun peut acheter et juger. La valeur de cette nouvelle identite depend alors non plus de nous, mais de la reaction des autres.
TEXT,
                ],
            ],
        ],
        '002' => [
            'number' => '002',
            'title' => 'Société parfaite ?',
            'slug' => 'salle-002.php',
            'heroImage' => 'assets/images/carousel-expo-2.jpg',
            'supportLabel' => 'TP 1.2 - Bon profil / Antithèse / Beauté hors du cadre',
            'summary' => 'Trois œuvres interactives sur les deepfakes, les biais médiatiques et la façon dont les écrans transforment notre perception du réel.',
            'question' => 'Comment les objets numériques altèrent-ils notre perception du réel ?',
            'focus' => 'Image de soi, médias et perception',
            'concept' => <<<'TEXT'
A travers la creation de trois oeuvres interactives, nous cherchons a explorer la maniere dont les technologies influencent notre perception du reel, en transformant notre rapport au corps, a l'image et a l'environnement.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Bon profil',
                    'description' => <<<'TEXT'
L'oeuvre explore la vulnerabilite de notre identite numerique en placant le visiteur au coeur d'une mecanique de desinformation instantanee. Le spectateur est d'abord invite a prendre une simple photo, pensant capturer un souvenir inoffensif. Cependant, cette image est immediatement detournee par une intelligence artificielle qui genere un deepfake a son insu. En quelques secondes, le visage du visiteur se retrouve propulse dans des situations absurdes ou compromettantes et publie sur un faux fil d'actualite de reseaux sociaux.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'Antithese',
                    'description' => <<<'TEXT'
Antithese oppose deux visions de la realite : une vision deformee, influencee par les reseaux sociaux, les chaines d'information et les contenus biaises, et une vision plus objective basee sur des faits, des chiffres et l'esprit critique. Grace a une interaction basee sur la distance du visiteur, l'installation montre que notre perception peut evoluer selon le point de vue adopte. L'oeuvre invite ainsi le spectateur a prendre du recul face aux informations qu'il consomme quotidiennement et a questionner la maniere dont les medias influencent sa comprehension du reel.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'Beaute hors du cadre',
                    'description' => <<<'TEXT'
L'oeuvre plonge le spectateur dans un espace vegetal apaisant ou un ecran geant imite un smartphone. Ce miroir numerique capte le reflet du visiteur et l'invite a swiper des videos d'abord familiers et agreables. Au fil des defilements, les contenus deviennent angoissants, la lumiere s'assombrit et les sons naturels se deforment pour devenir inquietants. Le reflet de l'utilisateur s'efface alors peu a peu, donnant l'illusion qu'il est totalement absorbe par l'ecran. Cette installation interactive offre ainsi une metaphore de la mort numerique. Elle illustre comment notre usage des objets numeriques et des reseaux sociaux altere notre perception du reel.
TEXT,
                ],
            ],
        ],
        '005' => [
            'number' => '005',
            'title' => 'Présences augmentées',
            'slug' => 'salle-005.php',
            'heroImage' => 'assets/images/carousel-expo-3.jpg',
            'supportLabel' => 'TP 2.1 - Tapis Rouge / En Direct / AD-HD',
            'summary' => 'Une critique de la société du spectacle, du jugement permanent et de l’économie de l’attention.',
            'question' => "Comment l'illusion d'une societe parfaite revele-t-elle l'etat de la nôtre ?",
            'focus' => 'Regard social, consommation et attention',
            'concept' => <<<'TEXT'
Le theme principal de notre salle est de questionner les facades que la societe se construit pour masquer ses contradictions, qu'il s'agisse du regard social sur les reseaux, du glamour de la mode ou de la mecanique de la consommation. Les trois oeuvres montrent ainsi comment le numerique, en mettant en scene ces illusions, finit par reveler l'etat reel d'un monde qui se reve parfait.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Tapis Rouge',
                    'description' => <<<'TEXT'
Tapis Rouge est une installation interactive et immersive qui detourne les codes du prestige pour confronter le spectateur aux realites sociales invisibles de la production industrielle.

Un tapis rouge physique invite le public a s'avancer sous des projecteurs de scene. Le mouvement du visiteur controle directement une video projetee sur le mur frontal : d'abord une ambiance de luxe et de privilege puis, a mesure qu'il avance, les coulisses de la consommation s'imposent : entrepots, lignes de production, infrastructures froides.

En bout de course, l'image devient grave : penibilite du travail, insalubrite des usines, epuisement des corps. L'oeuvre revele que chaque pas vers le succes repose sur une realite humaine sacrifiee.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'En Direct',
                    'description' => <<<'TEXT'
Placez-vous devant l'ecran : une camera vous filme en direct, a la maniere d'un live TikTok ou Instagram. Des commentaires apparaissent automatiquement, generes selon votre distance a l'ecran et vos expressions faciales detectees par reconnaissance d'image. Trop proche, trop loin, souriant ou neutre - quoi que vous fassiez, vous serez juge. En Direct met en scene le jugement social permanent que produisent les reseaux sociaux, et l'illusion d'un like qui n'existe que pour mieux nous critiquer.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'AD-HD',
                    'description' => <<<'TEXT'
Ad Driven Human Display est une oeuvre interactive qui questionne la place de l'humain dans l'economie de l'attention. Face a une interface TikTok, le visiteur fait defiler un flux de contenus promotionnels a l'aide d'un grand rouleau physique. Des pop-up publicitaires apparaissent aleatoirement, l'obligeant a les fermer avec une souris. L'oeuvre revele une illusion contemporaine : nous pensons controler ce que nous regardons, alors que nos gestes, notre temps et notre attention sont constamment captes par la publicite.
TEXT,
                ],
            ],
        ],
        '021' => [
            'number' => '021',
            'title' => 'Identités numériques',
            'slug' => 'salle-021.php',
            'heroImage' => 'assets/images/carousel-expo-4.webp',
            'supportLabel' => "TP 2.2 - Lotus / E-biscus / Datura / œuvre 4",
            'summary' => 'Une immersion onirique où les écrans deviennent des fenêtres vers l’inconscient et où chaque interaction déforme la réalité.',
            'question' => "À l'image de nos rêves, comment le numérique altère-t-il notre perception de la réalité ?",
            'focus' => 'Rêves, inconscient et réalité altérée',
            'concept' => <<<'TEXT'
Notre exposition immersive et interactive vous invite a explorer comment le numerique transforme notre perception du monde. Grace a des oeuvres numeriques innovantes, vous serez transporte·e dans des univers oniriques ou les ecrans deviennent des fenetres vers l'inconscient, et ou chaque interaction revele une nouvelle facette de notre realite alteree.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Lotus',
                    'description' => <<<'TEXT'
Nous creons une oeuvre interactive ou la chute d'Alice au pays des merveilles dans le terrier du lapin devient une experience sensorielle et participative. En jouant du synthetiseur, nous transformons en temps reel ce que le public voit a l'ecran : les notes jouees modifient la vitesse, la forme ou les couleurs de la chute, comme si Alice reagissait directement aux sons. Une deuxieme personne peut interagir avec un LFO pour ajouter une couche de modulation, deformant a la fois la sonorite du synthe et l'image, tandis qu'une pedale de sustain permet de ralentir ou d'etirer la chute, creant une illusion de controle sur ce conte classique.

Notre objectif est de brouiller les frontieres entre realite et virtualite, en faisant du spectateur un co-createur de l'oeuvre. TouchDesigner nous sert de coeur technique pour lier le MIDI du synthe aux effets visuels, tandis que Blender nous permet de modeliser Alice et son environnement. En combinant son, image et interaction tactile, nous explorons le theme E-LLUSION a travers une performance collective ou la technologie devient le pinceau d'une chute onirique et deformee.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'E-biscus',
                    'description' => <<<'TEXT'
E-biscus est une oeuvre interactive qui permet de mettre en avant les illusions de la societe, plus precisement concernant la beaute. Le but global de l'oeuvre est de plonger le spectateur dans un reve numerique. Un jeune homme peu sur de lui s'endort apres avoir like la photo d'une inconnue belle aux yeux de la societe, sur Instagram. La suite se passe dans son reve : il a un rendez-vous organise avec cette jolie inconnue et il doit preparer son apparence. C'est a ce moment que les spectateurs peuvent interagir avec l'oeuvre. Une interface s'ouvre pour modifier le jeune homme. Une vue de face du jeune homme apparait et les spectateurs peuvent maintenant le modifier : les muscles, les cheveux, la barbe, des tatouages... Une fois cette transformation validee par les spectateurs, le date peut commencer. La rencontre se passe, mais pas comme prevu, car en effet la fille ne ressemble pas a ses photos et le jeune homme non plus, les deux sont decus. L'oeuvre E-biscus interroge sur la difference entre l'image que l'on donne sur les reseaux sociaux et ce que l'on est reellement.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'Datura',
                    'description' => <<<'TEXT'
« Datura » est une installation interactive presentant une foret de sequoias en 3D, projetee sur un grand ecran. Deux zones physiques au sol devant la projection, « Zone Reve » et « Zone Cauchemar », invitent le spectateur a interagir. Une webcam detecte la repartition des visiteurs entre ces deux zones. Le public, par sa presence physique, vote et influence en temps reel le monde 3.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 4',
                    'title' => 'Oeuvre 4',
                    'description' => <<<'TEXT'
Le tableur mentionne cette quatrieme oeuvre, mais aucun texte descriptif n'y est renseigne.
TEXT,
                ],
            ],
        ],
    ];

    $roomOrder = [
        '001' => '021',
        '002' => '005',
        '005' => '001',
        '021' => '002',
    ];

    $roomGroups = [
        '001' => 'La Pépinière',
        '002' => "L'Envers du Décors",
        '005' => 'Horizon',
        '021' => 'Societ-e',
    ];

    $roomSupportLabels = [
        '001' => 'Identités numériques â€” TP 2.2 - Lotus / E-biscus / Datura / Å“uvre 4',
        '002' => 'Présences augmentées â€” TP 2.1 - Tapis Rouge / En Direct / AD-HD',
        '005' => 'Miroirs numériques â€” TP 1.1 - Community / Distorsion',
        '021' => 'Société parfaite ? â€” TP 1.2 - Bon profil / Antithèse / Beauté hors du cadre',
    ];

    $catalog = [];

    foreach ($roomOrder as $number => $sourceNumber) {
        $room = $sourceCatalog[$sourceNumber];
        $room['number'] = $number;
        $room['slug'] = 'salle-' . $number . '.php';
        $room['title'] = $roomGroups[$number];
        $room['supportLabel'] = $roomSupportLabels[$number];
        $catalog[$number] = $room;
    }

    return $catalog;
}

function getRoomNumbers(): array
{
    return array_keys(getRoomCatalog());
}

function getRoomByNumber(string $number): ?array
{
    $catalog = getRoomCatalog();

    return $catalog[$number] ?? null;
}

function getHomeRoomCards(): array
{
    $catalog = getRoomCatalog();

    return [
        [
            'number' => $catalog['001']['number'],
            'title' => $catalog['001']['title'],
            'description' => $catalog['001']['summary'],
            'href' => $catalog['001']['slug'],
            'badge' => $catalog['001']['supportLabel'],
        ],
        [
            'number' => $catalog['002']['number'],
            'title' => $catalog['002']['title'],
            'description' => $catalog['002']['summary'],
            'href' => $catalog['002']['slug'],
            'badge' => $catalog['002']['supportLabel'],
        ],
        [
            'number' => $catalog['005']['number'],
            'title' => $catalog['005']['title'],
            'description' => $catalog['005']['summary'],
            'href' => $catalog['005']['slug'],
            'badge' => $catalog['005']['supportLabel'],
        ],
        [
            'number' => $catalog['021']['number'],
            'title' => $catalog['021']['title'],
            'description' => $catalog['021']['summary'],
            'href' => $catalog['021']['slug'],
            'badge' => $catalog['021']['supportLabel'],
        ],
    ];
}
