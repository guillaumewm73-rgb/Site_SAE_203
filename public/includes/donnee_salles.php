<?php

declare(strict_types=1);

function getRoomCatalog(): array
{
    $sourceCatalog = [
        '021' => [
            'number' => '021',
            'title' => 'Societ-e',
            'slug' => 'salle-021.php',
            'heroImage' => 'assets/images/carousel-expo-1.jpg',
            'supportLabel' => 'TP 1.1 - Community / Distorsion',
            'summary' => 'Deux œuvres autour de l’image altérée, du regard collectif et de ce qui nous échappe quand notre visage devient une matière à manipuler.',
            'question' => 'Comment une image devient-elle une matière collective ?',
            'focus' => 'Identité, image et regard collectif',
            'concept' => <<<'TEXT'
L'oeuvre montre que, dans l'univers numérique, notre visage devient une matière que les autres peuvent modifier, détourner ou réinventer. Cette transformation imposée crée une version de nous qui échappe à notre contrôle.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Community',
                    'description' => <<<'TEXT'
Aucune description disponible.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'Distorsion',
                    'description' => <<<'TEXT'
L'oeuvre Distorsion explore l'émancipation de notre identité dans un récit où notre image ne nous appartient plus. Elle montre que, dans l'univers numérique, notre visage devient une matière que les autres peuvent modifier, détourner ou réinventer. Cette transformation imposée crée une version de nous qui échappe à notre contrôle. Dans Distorsion, cette image altérée est ensuite mise en vente, comme un produit parmi d'autres, révélant comment la société de consommation s'approprie jusqu'à notre identité. Le visage devient un objet marchand, façonné par le regard collectif, que chacun peut acheter et juger. La valeur de cette nouvelle identité dépend alors non plus de nous, mais de la réaction des autres.
TEXT,
                ],
            ],
        ],
        '005' => [
            'number' => '005',
            'title' => 'Horizon',
            'slug' => 'salle-005.php',
            'heroImage' => 'assets/images/carousel-expo-2.jpg',
            'supportLabel' => 'TP 1.2 - Bon profil / Antithèse / Beauté hors du cadre',
            'summary' => 'Trois œuvres interactives sur les deepfakes, les biais médiatiques et la façon dont les écrans transforment notre perception du réel.',
            'question' => 'Comment les objets numériques altèrent-ils notre perception du réel ?',
            'focus' => 'Image de soi, médias et perception',
            'concept' => <<<'TEXT'
À travers la création de trois oeuvres interactives, nous cherchons à explorer la manière dont les technologies influencent notre perception du réel, en transformant notre rapport au corps, à l'image et à l'environnement.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Bon profil',
                    'description' => <<<'TEXT'
L'oeuvre explore la vulnérabilite de notre identité numérique en placant le visiteur au coeur d'une mécanique de désinformation instantanée. Le spectateur est d'abord invité à prendre une simple photo, pensant capturer un souvenir inoffensif. Cependant, cette image est immédiatement detournée par une intelligence artificielle qui génère un deepfake à son insu. En quelques secondes, le visage du visiteur se retrouve propulse dans des situations absurdes ou compromettantes et publie sur un faux fil d'actualité de réseaux sociaux.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'Antithèse',
                    'description' => <<<'TEXT'
Antithèse oppose deux visions de la réalité : une vision déformée, influencée par les réseaux sociaux, les chaînes d'information et les contenus biaisés, et une vision plus objective basée sur des faits, des chiffres et l'esprit critique. Grâce à une interaction basée sur la distance du visiteur, l'installation montre que notre perception peut évoluer selon le point de vue adopté. L'oeuvre invite ainsi le spectateur à prendre du recul face aux informations qu'il consomme quotidiennement et à questionner la manière dont les medias influencent sa compréhension du réel.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'Beauté hors du cadre',
                    'description' => <<<'TEXT'
L'oeuvre plonge le spectateur dans un espace végétal apaisant ou un écran géant imite un smartphone. Ce miroir numerique capte le reflet du visiteur et l'invite a swiper des vidéos d'abord familiers et agréables. Au fil des defilements, les contenus deviennent angoissants, la lumiere s'assombrit et les sons naturels se deforment pour devenir inquiétants. Le reflet de l'utilisateur s'efface alors peu à peu, donnant l'illusion qu'il est totalement absorbé par l'écran. Cette installation interactive offre ainsi une métaphore de la mort numérique. Elle illustre comment notre usage des objets numériques et des réseaux sociaux altère notre perception du réel.
TEXT,
                ],
            ],
        ],
        '002' => [
            'number' => '002',
            'title' => 'L’Envers du Décors',
            'slug' => 'salle-002.php',
            'heroImage' => 'assets/images/carousel-expo-3.jpg',
            'supportLabel' => 'TP 2.1 - Tapis Rouge / En Direct / AD-HD',
            'summary' => 'Une critique de la société du spectacle, du jugement permanent et de l’économie de l’attention.',
            'question' => "Comment l'illusion d'une société parfaite révèle-t-elle l'état de la nôtre ?",
            'focus' => 'Regard social, consommation et attention',
            'concept' => <<<'TEXT'
Le thème principal de notre salle est de questionner les facades que la société se construit pour masquer ses contradictions, qu'il s'agisse du regard social sur les réseaux sociaux, du glamour de la mode ou de la mécanique de la consommation. Les trois oeuvres montrent ainsi comment le numérique, en mettant en scène ces illusions, finit par révéler l'état réel d'un monde qui se rêve parfait.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Tapis Rouge',
                    'description' => <<<'TEXT'
Tapis Rouge est une installation interactive et immersive qui détourne les codes du prestige pour confronter le spectateur aux réalités sociales invisibles de la production industrielle.

Un tapis rouge physique invite le public à s'avancer sous des projecteurs de scène. Le mouvement du visiteur contrôle directement une vidéo projetée sur le mur frontal : d'abord une ambiance de luxe et de privilège puis, à mesure qu'il avance, les coulisses de la consommation s'imposent : entrepôts, lignes de production, infrastructures froides.

En bout de course, l'image devient grave : pénibilité du travail, insalubrité des usines, épuisement des corps. L'oeuvre révèle que chaque pas vers le succès repose sur une réalité humaine sacrifiée.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'En Direct',
                    'description' => <<<'TEXT'
Placez-vous devant l'écran : une caméra vous filme en direct, à la manière d'un live TikTok ou Instagram. Des commentaires apparaissent automatiquement, générés selon votre distance à l'écran et vos expressions faciales détectées par reconnaissance d'image. Trop proche, trop loin, souriant ou neutre - quoi que vous fassiez, vous serez jugés. En Direct met en scène le jugement social permanent que produisent les réseaux sociaux, et l'illusion d'un like qui n'existe que pour mieux nous critiquer.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'AD-HD',
                    'description' => <<<'TEXT'
Ad Driven Human Display est une oeuvre interactive qui questionne la place de l'humain dans l'économie de l'attention. Face à une interface TikTok, le visiteur fait défiler un flux de contenus promotionnels à l'aide d'un grand rouleau physique. Des pop-up publicitaires apparaissent aléatoirement, l'obligeant à les fermer avec une souris. L'oeuvre révèle une illusion contemporaine : nous pensons contrôler ce que nous regardons, alors que nos gestes, notre temps et notre attention sont constamment captés par la publicité.
TEXT,
                ],
            ],
        ],
        '001' => [
            'number' => '001',
            'title' => 'La Pépinière',
            'slug' => 'salle-001.php',
            'heroImage' => 'assets/images/carousel-expo-4.webp',
            'supportLabel' => "TP 2.2 - Lotus / E-biscus / Datura / œuvre 4",
            'summary' => 'Une immersion onirique où les écrans deviennent des fenêtres vers l’inconscient et où chaque interaction déforme la réalité.',
            'question' => "À l'image de nos rêves, comment le numérique altère-t-il notre perception de la réalité ?",
            'focus' => 'Rêves, inconscient et réalité altérée',
            'concept' => <<<'TEXT'
Notre exposition immersive et interactive vous invite à explorer comment le numérique transforme notre perception du monde. Grâce a des oeuvres numériques innovantes, vous serez transporté·e dans des univers oniriques où les écrans deviennent des fenêtres vers l'inconscient, et où chaque interaction révèle une nouvelle facette de notre réalité altérée.
TEXT,
            'works' => [
                [
                    'group' => 'Sous-groupe 1',
                    'title' => 'Lotus',
                    'description' => <<<'TEXT'
Nous créons une oeuvre interactive où la chute d'Alice au pays des merveilles dans le terrier du lapin devient une expérience sensorielle et participative. En jouant du synthétiseur, nous transformons en temps réel ce que le public voit à l'écran : les notes jouées modifient la vitesse, la forme ou les couleurs de la chute, comme si Alice réagissait directement aux sons. Une deuxième personne peut interagir avec un LFO pour ajouter une couche de modulation, déformant à la fois la sonorité du synthé et l'image, tandis qu'une pédale de sustain permet de ralentir ou d'étirer la chute, créant une illusion de contrôle sur ce conte classique.

Notre objectif est de brouiller les frontières entre réalité et virtualité, en faisant du spectateur un co-createur de l'oeuvre. TouchDesigner nous sert de coeur technique pour lier le MIDI du synthé aux effets visuels, tandis que Blender nous permet de modeliser Alice et son environnement. En combinant son, image et interaction tactile, nous explorons le thème E-LLUSION à travers une performance collective où la technologie devient le pinceau d'une chute onirique et déformée.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 2',
                    'title' => 'E-biscus',
                    'description' => <<<'TEXT'
E-biscus est une oeuvre interactive qui permet de mettre en avant les illusions de la société, plus précisement concernant la beauté. Le but global de l'oeuvre est de plonger le spectateur dans un rêve numérique. Un jeune homme peu sur de lui s'endort après avoir liké la photo d'une inconnue belle aux yeux de la société, sur Instagram. La suite se passe dans son rêve : il a un rendez-vous organisé avec cette jolie inconnue et il doit préparer son apparence. C'est à ce moment que les spectateurs peuvent interagir avec l'oeuvre. Une interface s'ouvre pour modifier le jeune homme. Une vue de face du jeune homme apparait et les spectateurs peuvent maintenant le modifier : les muscles, les cheveux, la barbe, des tatouages... Une fois cette transformation validée par les spectateurs, le date peut commencer. La rencontre se passe, mais pas comme prévu, car en effet la fille ne ressemble pas à ses photos et le jeune homme non plus, les deux sont déçus. L'oeuvre E-biscus interroge sur la difference entre l'image que l'on donne sur les réseaux sociaux et ce que l'on est réellement.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 3',
                    'title' => 'Datura',
                    'description' => <<<'TEXT'
« Datura » est une installation interactive présentant une forêt de sequoias en 3D, projetee sur un grand écran. Deux zones physiques au sol devant la projection, « Zone Rêve » et « Zone Cauchemar », invitent le spectateur à interagir. Une webcam détecte la répartition des visiteurs entre ces deux zones. Le public, par sa présence physique, vote et influence en temps réel le monde 3.
TEXT,
                ],
                [
                    'group' => 'Sous-groupe 4',
                    'title' => 'Oeuvre 4',
                    'description' => <<<'TEXT'
Aucune description disponible.
TEXT,
                ],
            ],
        ],
    ];

  $roomOrder = [
        '001' => '001',
        '002' => '002',
        '005' => '005',
        '021' => '021',
    ];

    $roomGroups = [
        '001' => 'La Pépinière',
        '002' => "L'Envers du Décors",
        '005' => 'Horizon',
        '021' => 'Societ-e',
    ];  

    $roomSupportLabels = [
        '001' => 'TP 2.2 - Lotus / E-biscus / Datura / Oeuvre 4',
        '002' => 'TP 2.1 - Tapis Rouge / En Direct / AD-HD',
        '005' => 'TP 1.2 - Bon profil / Antithèse / Beauté hors du cadre',
        '021' => 'TP 1.1 - Community / Distorsion',
    ];

    $roomReferents = [
        '001' => [
            'label' => 'Référent TP 2.2',
            'name' => 'Kylian Provot',
            'contact' => 'kylian.provot@etu.univ-smb.fr',
            'href' => 'mailto:kylian.provot@etu.univ-smb.fr',
        ],
        '002' => [
            'label' => 'Référent TP 2.1',
            'name' => 'Guillaume Willaime Moulin',
            'contact' => 'Guillaume.Willaime-Moulin@etu.univ-smb.fr',
            'href' => 'mailto:Guillaume.Willaime-Moulin@etu.univ-smb.fr',
        ],
        '005' => [
            'label' => 'Référente TP 1.2',
            'name' => 'Cynthia Peinnet',
            'contact' => 'cpeinnet@aol.com',
            'href' => 'mailto:cpeinnet@aol.com',
        ],
        '021' => [
            'label' => 'Référent TP 1.1',
            'name' => 'Benjamin Renollet',
            'contact' => '06 51 16 82 42',
            'href' => 'tel:+33651168242',
        ],
    ];

    $catalog = [];

    foreach ($roomOrder as $number => $sourceNumber) {
        $room = $sourceCatalog[$sourceNumber];
        $room['number'] = $number;
        $room['slug'] = 'salle-' . $number . '.php';
        $room['title'] = $roomGroups[$number];
        $room['supportLabel'] = $roomSupportLabels[$number];
        $room['referent'] = $roomReferents[$number];
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
