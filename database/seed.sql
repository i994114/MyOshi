-- ===================================
-- users
-- ===================================

INSERT INTO users (
    id, name, email, password, gender, age, pic, delete_flg, login_time, create_date
) VALUES
(1, '山田 太郎', 'taro@example.com', 'password', 0, 30, 'user1.jpg', 0, NOW(), NOW()),
(2, '佐藤 花子', 'hanako@example.com', 'password', 1, 28, 'user2.jpg', 0, NOW(), NOW());


-- ===================================
-- categories
-- ===================================

INSERT INTO categories (
    id, name, create_date
) VALUES
(1, '合同稽古', NOW()),
(2, '練習試合', NOW()),
(3, '大会', NOW()),
(4, '講習会', NOW()),
(5, 'その他', NOW());


-- ===================================
-- targets
-- ===================================

INSERT INTO targets (
    id, name, create_date
) VALUES
(1, '小学生', NOW()),
(2, '中学生', NOW()),
(3, '高校生', NOW()),
(4, '大学生', NOW()),
(5, '一般', NOW());


-- ===================================
-- events
-- ===================================

INSERT INTO events (
    id,
    name,
    category_id,
    user_id,
    prefecture_id,
    event_date,
    start_time,
    end_time,
    description,
    pic1,
    pic2,
    pic3,
    delete_flg,
    create_date
) VALUES
(
    1,
    '宇都宮合同稽古会',
    1,
    1,
    9,
    '2026-08-15',
    '10:00:00',
    '12:00:00',
    '初心者歓迎。地稽古中心です。',
    'event1_1.jpg',
    'event1_2.jpg',
    'event1_3.jpg',
    0,
    NOW()
),
(
    2,
    '栃木交流稽古会',
    5,
    2,
    9,
    '2026-08-23',
    NULL,
    NULL,
    '久しぶりに剣道を楽しみたい方向け。',
    'event2_1.jpg',
    'event2_2.jpg',
    'event2_3.jpg',
    0,
    NOW()
);

-- ===================================
-- event_targets
-- ===================================

INSERT INTO event_targets (
    event_id,
    target_id
) VALUES
(1, 5),
(2, 4),
(2, 5);


-- ===================================
-- boards
-- ===================================

INSERT INTO boards (
    id,
    user_id,
    event_id,
    type,
    delete_flg,
    create_date
) VALUES
(1, 1, 1, 0, 0, NOW()),
(2, 2, 2, 0, 0, NOW());


-- ===================================
-- messages
-- ===================================

INSERT INTO messages (
    id,
    board_id,
    from_user,
    to_user,
    content,
    delete_flg,
    create_date
) VALUES
(
    1,
    1,
    1,
    2,
    '初心者の方もお気軽にご参加ください！',
    0,
    NOW()
),
(
    2,
    1,
    2,
    1,
    '参加予定です。よろしくお願いします。',
    0,
    NOW()
),
(
    3,
    2,
    2,
    1,
    '途中参加もOKです。',
    0,
    NOW()
);

-- ===================================
-- favorites
-- ===================================

INSERT INTO favorites (
    id,
    user_id,
    event_id,
    create_date
) VALUES
(1, 1, 2, NOW()),
(2, 2, 1, NOW());

