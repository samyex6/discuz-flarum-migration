<?php

class Mapping {
    const TAG_NEWS         = 1;  const TAG_GAME             = 2;  const TAG_GAME_DB          = 3;  const TAG_GAME_DISCUSS   = 4;
    const TAG_GAME_BATTLE  = 5;  const TAG_GAME_GIVEAWAY    = 6;  const TAG_GAME_GO          = 7;  const TAG_GAME_SHUFFLE   = 8;
    const TAG_GAME_USUM    = 9;  const TAG_GAME_SM          = 10; const TAG_GAME_ORAS        = 11; const TAG_GAME_XY        = 12;
    const TAG_GAME_BW2     = 13; const TAG_GAME_BW          = 14; const TAG_GAME_HGSS        = 15; const TAG_GAME_DPP       = 16;
    const TAG_GAME_OLD     = 17; const TAG_GAME_TCG         = 18; const TAG_GAME_OTHER       = 19; const TAG_GENERAL        = 20;
    const TAG_GENERAL_CHAT = 21; const TAG_GENERAL_WRITE    = 22; const TAG_GENERAL_DRAW     = 23; const TAG_GENERAL_MEDIA  = 24;
    const TAG_SITE         = 25; const TAG_SITE_GENERAL     = 26; const TAG_SITE_MOD         = 27; const TAG_POKEMON        = 28;
    const TAG_ARCHIVE      = 29; const TAG_ARCHIVE_RUMBLE13 = 30; const TAG_ARCHIVE_RUMBLE14 = 31; const TAG_ARCHIVE_REPORT = 32;
    const TAG_SITE_ANN     = 33;


    public static $tags_info = [
        self::TAG_NEWS => ['slug' => 'news', 'name' => '情报', 'children' => []],
        self::TAG_GAME => ['slug' => 'game', 'name' => '游戏', 'children' => [
            self::TAG_GAME_DB       => ['slug' => 'game-database', 'name' => '资料'],
            self::TAG_GAME_DISCUSS  => ['slug' => 'game-discuss' , 'name' => '讨论/问题'],
            self::TAG_GAME_BATTLE   => ['slug' => 'game-battle'  , 'name' => '对战'],
            self::TAG_GAME_GIVEAWAY => ['slug' => 'game-giveaway', 'name' => '派送'],
            self::TAG_GAME_GO       => ['slug' => 'game-go'      , 'name' => 'Pokemon Go'],
            self::TAG_GAME_SHUFFLE  => ['slug' => 'game-shuffle' , 'name' => '三消'],
            self::TAG_GAME_USUM     => ['slug' => 'game-usum'    , 'name' => '究级太阳月亮'],
            self::TAG_GAME_SM       => ['slug' => 'game-sm'      , 'name' => '太阳月亮'],
            self::TAG_GAME_ORAS     => ['slug' => 'game-oras'    , 'name' => 'ORAS'],
            self::TAG_GAME_XY       => ['slug' => 'game-xy'      , 'name' => 'XY'],
            self::TAG_GAME_BW2      => ['slug' => 'game-bw2'     , 'name' => '黑白2'],
            self::TAG_GAME_BW       => ['slug' => 'game-bw'      , 'name' => '黑白'],
            self::TAG_GAME_HGSS     => ['slug' => 'game-hgss'    , 'name' => '心金魂银'],
            self::TAG_GAME_DPP      => ['slug' => 'game-dpp'     , 'name' => '珍珠钻石白金'],
            self::TAG_GAME_OLD      => ['slug' => 'game-old'     , 'name' => '1-3代'],
            self::TAG_GAME_TCG      => ['slug' => 'game-tcg'     , 'name' => 'TCG'],
            self::TAG_GAME_OTHER    => ['slug' => 'game-other'   , 'name' => '其它']
        ]],
        self::TAG_GENERAL => ['slug' => 'general', 'name' => '综合', 'children' => [
            self::TAG_GENERAL_CHAT  => ['slug' => 'general-chat'    , 'name' => '聊天'],
            self::TAG_GENERAL_WRITE => ['slug' => 'general-literacy', 'name' => '写作'],
            self::TAG_GENERAL_DRAW  => ['slug' => 'general-drawing' , 'name' => '画图'],
            self::TAG_GENERAL_MEDIA => ['slug' => 'general-media'   , 'name' => '影音']
        ]],
        self::TAG_SITE => ['slug' => 'site', 'name' => '站务', 'children' => [
            self::TAG_SITE_ANN     => ['slug' => 'site-announcement', 'name' => '公告'],
            self::TAG_SITE_GENERAL => ['slug' => 'site-general'     , 'name' => '反馈/申请'],
            self::TAG_SITE_MOD     => ['slug' => 'site-moderation'  , 'name' => '管理']
        ]],
        self::TAG_POKEMON => ['slug' => 'pokemon', 'name' => '养成', 'children' => []],
        self::TAG_ARCHIVE => ['slug' => 'archive', 'name' => '归档', 'children' => [
            self::TAG_ARCHIVE_RUMBLE13 => ['slug' => 'archive-rumble13', 'name' => '大乱斗13'],
            self::TAG_ARCHIVE_RUMBLE14 => ['slug' => 'archive-rumble14', 'name' => '大乱斗14'],
            self::TAG_ARCHIVE_REPORT   => ['slug' => 'archive-report'  , 'name' => '战报']
        ]]
    ];

    public static $forum_to_tags = [
        72  => [self::TAG_NEWS], // 新闻区
        89  => [self::TAG_NEWS], // 主机讨论区

        115 => [self::TAG_GAME, self::TAG_GAME_BATTLE],             // 对战区
        118 => [self::TAG_GAME, self::TAG_GAME_GIVEAWAY],           // 派送区
        112 => [self::TAG_GAME, self::TAG_GAME_DISCUSS],            // 问答区
        114 => [self::TAG_GAME, self::TAG_GAME_GO],                 // Pokemon Go
        116 => [self::TAG_GAME, self::TAG_GAME_SHUFFLE],            // Pokemon Shuffle
        77  => [self::TAG_GAME, self::TAG_GAME_DB],                 // 原创专区
        78  => [self::TAG_GAME, self::TAG_GAME_DB],                 // 口袋讲座
        113 => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_USUM],    // 资料区
        109 => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_SM],      // SM资料库
        107 => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_ORAS],    // ORAS资料库
        103 => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_XY],      // X/Y资料库
        94  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_BW2],     // 黑/白Ⅱ资料库
        71  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_BW],      // 黑/白资料库
        41  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_HGSS],    // 心金|魂银资料库
        42  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_DPP],     // 珍珠/钻石/白金资料库
        43  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_OLD],     // GBA资料库
        92  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_OLD],     // 旧版资料库
        117 => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_TCG],     // TCG资料库
        93  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_OTHER],   // 迷宫/探险队资料库
        44  => [self::TAG_GAME, self::TAG_GAME_DB, self::TAG_GAME_OTHER],   // 护林员资料库

        6   => [self::TAG_GENERAL, self::TAG_GENERAL_CHAT],     // 清风茶馆
        60  => [self::TAG_GENERAL, self::TAG_GENERAL_CHAT],     // PC乐园
        40  => [self::TAG_GENERAL, self::TAG_GENERAL_CHAT],     // PU.报刊
        52  => [self::TAG_GENERAL, self::TAG_GENERAL_DRAW],     // 七彩画廊
        53  => [self::TAG_GENERAL, self::TAG_GENERAL_WRITE],    // 文墨工坊
        54  => [self::TAG_GENERAL, self::TAG_GENERAL_MEDIA],    // 影音交流

        4   => [self::TAG_SITE                    ], // 站务交流
        21  => [self::TAG_SITE, self::TAG_SITE_MOD], // 内部研究
        63  => [self::TAG_SITE, self::TAG_SITE_MOD], // 秘密研究所
        81  => [self::TAG_SITE, self::TAG_SITE_MOD], // 小花园

        111 => [self::TAG_ARCHIVE],                     // 论坛归档
        104 => [self::TAG_ARCHIVE, self::TAG_ARCHIVE_RUMBLE13], // 口袋大乱斗2013
        105 => [self::TAG_ARCHIVE, self::TAG_ARCHIVE_RUMBLE14], // 口袋大乱斗2014
        20  => [self::TAG_ARCHIVE, self::TAG_ARCHIVE_REPORT],   // PBO|PO|NB 战术/战报

        29  => [], // 宝可梦-主线
        11  => [], // 宝可梦-其它
        49  => [], // 大学社团
        3   => [], // 大学站务

        73  => [], // 口袋动画区【最新系列为XY】
        5   => [], // 新人学院
        64  => [], // 旧养成资料库
        97  => [], // 战斗地铁资料库
        80  => [], // 回收站
        8   => [], // 论坛养成
        62  => [], // 隐藏
    ];

    public static $threadtype_to_tags = [
        80 => self::TAG_GAME_USUM,    // 究级太阳/月亮
        44 => self::TAG_GAME_SM,      // 太阳/月亮
        45 => self::TAG_GAME_ORAS,    // 终极红宝石/始源蓝宝石
        46 => self::TAG_GAME_XY,      // X/Y
        47 => self::TAG_GAME_BW2,     // 黑II/白II
        48 => self::TAG_GAME_BW,      // 黑/白
        49 => self::TAG_GAME_OTHER,   // 信长的野望
        50 => self::TAG_GAME_HGSS,    // 心金/魂银
        51 => self::TAG_GAME_DPP,     // 珍珠/钻石/白金
        52 => self::TAG_GAME_OTHER,   // 迷宫/探险队
        53 => self::TAG_GAME_OTHER,   // 护林员
        54 => self::TAG_GAME_OLD,     // 红宝石/蓝宝石/绿宝石
        55 => self::TAG_GAME_OLD,     // 火红/叶绿
        56 => self::TAG_GAME_OLD,     // 红/蓝/绿/黄
        57 => self::TAG_GAME_OLD,     // 金/银/水晶
        58 => self::TAG_GAME_OTHER,   // 其它
        63 => self::TAG_SITE_GENERAL, // 建议/意见
        64 => self::TAG_SITE_GENERAL, // 申请
        65 => self::TAG_SITE_ANN      // 公告
    ];
}
