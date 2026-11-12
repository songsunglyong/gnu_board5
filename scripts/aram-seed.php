<?php
// 설치할 때만 읽는 아람북스 데모 초기 데이터.
$bookTabs = ['intro' => '책소개', 'features' => '특장점', 'activities' => '활용법', 'preview' => '미리보기'];
$series = [
    'baby' => ['베이비올', '0~3세', '오감으로 만나는 첫 그림책', [
        'baby' => ['베이비올 아기', '감각 놀이'],
        'art' => ['베이비올 명화 음악', '명화와 음악'],
        'science' => ['베이비올 수과학', '수와 과학'],
        'english' => ['플레이올 영어', '영어 놀이'],
        'nature' => ['자연이All', '자연 관찰'],
    ]],
    'kids' => ['키즈올', '유아', '질문이 자라는 그림책', [
        'aram' => ['우리 아람이', '국내 창작'],
        'mom' => ['아이맘콕', '마음과 감정'],
        'lala' => ['new!라라랜드', '세계 창작'],
        'hoho' => ['호호랜드', '이야기 읽기'],
        'math' => ['수학특공대', '놀이 수학'],
        'knowledge' => ['지식특공대', '생활 지식'],
        'science' => ['과학특공대', '과학 탐구'],
        'octopus' => ['문어빵', '문해력'],
        'lily' => ['리리의 이야기 숲', '세계 창작'],
        'coco' => ['코코랜드', '이야기 읽기'],
    ]],
    'school' => ['프리스쿨&스쿨', '예비 초등·초등', '한 걸음 더 넓은 세상', [
        'economy' => ['경제특공대', '생활 경제'],
        'eye' => ['EYE 그림책 심쿵', '마음과 감정'],
        'society' => ['참 똑똑한 사회씨', '사회 탐구'],
        'world' => ['세계 다글리', '세계 문화'],
        'people' => ['인물 세미나', '인물 이야기'],
        'myth' => ['그리스 로마 신화', '세계 신화'],
        'history' => ['아우라 한국사', '한국사'],
        'science' => ['원리 과학 도깨비', '과학 원리'],
        'classics' => ['명작문학', '세계 명작'],
    ]],
    'ort' => ['ORT', '영어 읽기', '이야기로 시작하는 영어 읽기', [
        'at-home' => ['ORT AT HOME', '단계별 영어'],
    ]],
];

$menu = [];
foreach ($series as $groupKey => [$label, $age, $tagline, $books]) {
    $items = [];
    foreach ($books as $key => [$title, $topic]) {
        $tabs = $bookTabs;
        switch ($groupKey . '/' . $key) {
            case 'baby/baby':
            case 'kids/lala':
                unset($tabs['preview']);
                break;
            case 'school/society':
                unset($tabs['activities']);
                break;
            case 'ort/at-home':
                $tabs = ['intro' => $bookTabs['intro']];
                break;
        }
        $items[$key] = ['title' => $title, 'kind' => 'book', 'topic' => $topic, 'children' => $tabs];
    }
    $menu[$groupKey] = ['title' => $label, 'age' => $age, 'tagline' => $tagline, 'items' => $items];
}

foreach ([
    'sets' => ['소전집', [
        'books' => ['소전집', 'collection', [
            'sos' => 'SOS 안전 동화(8권)', 'kids' => '개구쟁이 특공대(8권)', 'science' => '과학이랑(8권)',
            'leadership' => '교실 리더십 동화(8권)', 'dino' => '다이노 월드(8권)', 'maja' => '마자마자(8권)',
            'pet' => '매직펫 시리즈(7권)', 'banana' => '바나나 프리미엄(7권)', 'music' => '베토벤 음악 동화(8권)',
            'classics' => '별별 세계 명작(7권)', 'people' => '별별 인물 이야기(7권)', 'brunch' => '브런치 동화(8권)',
            'thinking' => '생각의 힘 꼬마 테스(8권)', 'environment' => '씨앗 환경동화(8권)', 'atong' => '아똥그림책(7권)',
            'job' => '아이 잡(job)(8권)', 'cooking' => '요리보고(8권)', 'social' => '자신만만 사회성동화(8권)',
            'joseon' => '조선왕조실록(10권)', 'play' => '짝짜꿍 그림책(8권)', 'habits' => '치카치카 생활동화(10권)',
            'language' => '코코아 언어(10권)', 'cognition' => '코코아 인지(9권)', 'emotion' => '코코아 정서(10권)',
            'plane' => '타요타요 비행기, 배(10권)', 'cars' => '타요타요 자동차(8권)', 'talmud' => '탈무드 동화(8권)',
            'education' => '행복한 성교육(8권)',
        ]],
        'tools' => ['교구', 'collection', [
            'big-book' => 'ABC 빅 북(1개)', 'abc' => 'ABC 코딱지북(24권)', 'music' => '뮤직통통(뮤직통통+디스크)',
            'banana' => '바나나 병풍 놀이북(1종)', 'sound' => '베이비올 소리블록(소리블록+정리 상자+맞추기 종이)',
            'blocks' => '아람 빌드 블록(24종+활동시트지 8장)',
        ]],
    ]],
    'community' => ['소통해요', [
        'notice' => ['공지사항', 'articles', ['app' => '아람이 앱 이용 안내', 'site' => '샘플 홈페이지 이용 안내']],
        'faq' => ['FAQ', 'faq', [
            'purchase' => '정품등록 및 구매', 'service' => 'AS 기준', 'quality' => '상품 품질',
            'usage' => '사용 방법', 'other' => '기타',
        ]],
        'resources' => ['자료실', 'articles', ['reading' => '가족 독서 기록 예시', 'activity' => '독후 활동 안내']],
        'press' => ['언론보도', 'articles', ['reading' => '가족 독서 프로그램 소개']],
        'events' => ['이벤트 및 행사', 'articles', ['reading' => '우리 가족 그림책 읽기', 'exhibit' => '그림책 전시 체험']],
        'consult' => ['제품상담신청', 'contact', []],
        'help' => ['고객센터', 'contact', []],
    ]],
    'about' => ['아람', [
        'company' => ['아람 소개', 'company', ['intro' => '아람북스', 'history' => '아람의 발자취']],
        'stores' => ['정품 판매처', 'stores', array_combine(
            explode(' ', 'seoul busan daegu incheon gwangju daejeon ulsan sejong gyeonggi gangwon chungbuk chungnam jeonbuk gyeongbuk gyeongnam jeju'),
            explode(' ', '서울 부산 대구 인천 전남광주 대전 울산 세종 경기 강원 충북 충남 전북 경북 경남 제주')
        )],
    ]],
] as $groupKey => [$label, $entries]) {
    $menu[$groupKey] = ['title' => $label, 'items' => []];
    foreach ($entries as $key => [$title, $kind, $children]) {
        $menu[$groupKey]['items'][$key] = compact('title', 'kind', 'children');
    }
}

$faq = [
    'purchase' => ['정품 판매처는 어디에서 찾나요?', '아람 메뉴의 정품 판매처에서 지역별 목록을 열 수 있습니다.'],
    'service' => ['도서 관련 문의는 어디에 남기나요?', '소통해요 메뉴의 고객센터에서 문의 경로를 확인할 수 있습니다.'],
    'quality' => ['수령한 도서에 이상이 있으면 어떻게 하나요?', '구매한 판매처에서 해당 도서의 교환 절차를 확인해 주세요.'],
    'usage' => ['전집은 어떻게 찾아볼 수 있나요?', '상단 도서검색에서 전집 이름이나 주제로 검색할 수 있습니다.'],
    'other' => ['어떤 전집부터 살펴보면 좋을까요?', '홈에서 연령대별 전집을 둘러볼 수 있습니다.'],
];
foreach ($menu as $groupKey => &$group) {
    foreach ($group['items'] as $itemKey => &$item) {
        $item['body'] = match ($item['kind']) {
            'book' => [
                'intro' => [$item['title'] . '은(는) ' . $item['topic'] . '을 주제로 소개하는 전집입니다.'],
                'features' => ['그림을 먼저 살펴보고, 페이지마다 발견한 것을 자기 말로 이야기해 보세요.'],
                'activities' => [
                    '표지를 보고 어떤 이야기가 펼쳐질지 상상해요.',
                    '함께 읽으며 좋아하는 장면을 골라요.',
                    '책 속에서 발견한 것을 그림이나 말로 표현해요.',
                ],
                'preview' => ['표지를 보고 이야기를 상상해요.', $item['topic']],
            ],
            'collection', 'articles' => array_map(
                static fn(string $title): string => $title . ($item['kind'] === 'collection' ? ' 상품을 소개하는 샘플입니다.' : '에 관한 소식을 보여주는 샘플입니다.'),
                $item['children']
            ),
            'faq' => $faq,
            'company' => [
                'intro' => '영유아와 어린이를 위한 그림책 전집을 만드는 출판사입니다.',
                'history' => ['2008년 도서출판 아람 설립', '2019년 아람북스 출범'],
            ],
            'stores' => ['seoul' => '서울 샘플 판매처', 'busan' => '부산 샘플 판매처', 'gyeonggi' => '경기 샘플 판매처'],
            'contact' => $itemKey === 'consult'
                ? ['전집 상담 안내', '전집을 고르고 정품 판매처를 확인해 보세요.']
                : ['문의 안내', '자주 묻는 질문에서 도서 이용 관련 안내를 찾아보세요.'],
        };
    }
    unset($item);
}
unset($group);

return ['_home' => [
    'title' => "그림책으로 자라는\n우리 아이",
    'intro' => "첫 그림책부터 세상을 넓히는 이야기까지,\n아이의 성장에 맞는 전집을 만나보세요.",
    'series_title' => '성장에 맞춰 만나는 아람 전집',
]] + $menu;
