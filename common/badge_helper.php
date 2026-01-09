<?php
/**
 * Badge Helper Functions for Quesiono
 */

/**
 * Ensures that the default badges exist in the database.
 */
function ensure_default_badges($conn) {
    $default_badges = [
        [
            'name' => 'Rising Star',
            'icon' => 'bi-star-fill',
            'description' => 'Awarded for contributing 5 or more answers.'
        ],
        [
            'name' => 'Quick Responder',
            'icon' => 'bi-lightning-fill',
            'description' => 'Awarded for providing 5 answers within 1 hour of the question being asked.'
        ],
        [
            'name' => 'Top Contributor',
            'icon' => 'bi-trophy-fill',
            'description' => 'Awarded for contributing 50 answers and 10 posts.'
        ]
    ];

    foreach ($default_badges as $badge) {
        $stmt = $conn->prepare("INSERT IGNORE INTO badges (name, icon, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $badge['name'], $badge['icon'], $badge['description']);
        $stmt->execute();
    }
}

/**
 * Checks if a user meets conditions for any badges and awards them.
 */
function check_and_award_badges($conn, $user_id) {
    // Ensure badges exist first
    ensure_default_badges($conn);

    // 1. Rising Star: minimum 5 answers
    $ansCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM answers WHERE user_id = ?");
    $ansCountQuery->bind_param("i", $user_id);
    $ansCountQuery->execute();
    $ansCount = $ansCountQuery->get_result()->fetch_assoc()['count'];

    if ($ansCount >= 5) {
        award_badge_if_not_exists($conn, $user_id, 'Rising Star');
    }

    // 2. Quick Responder: maximum time 1 hour to answer each question out of minimum 5 questions
    $quickCountQuery = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM answers a 
        JOIN questions q ON a.question_id = q.id 
        WHERE a.user_id = ? AND TIMESTAMPDIFF(MINUTE, q.created_at, a.created_at) <= 60
    ");
    $quickCountQuery->bind_param("i", $user_id);
    $quickCountQuery->execute();
    $quickCount = $quickCountQuery->get_result()->fetch_assoc()['count'];

    if ($quickCount >= 5) {
        award_badge_if_not_exists($conn, $user_id, 'Quick Responder');
    }

    // 3. Top Contributor: minimum 50 answers and 10 posts
    $postCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
    $postCountQuery->bind_param("i", $user_id);
    $postCountQuery->execute();
    $postCount = $postCountQuery->get_result()->fetch_assoc()['count'];

    if ($ansCount >= 50 && $postCount >= 10) {
        award_badge_if_not_exists($conn, $user_id, 'Top Contributor');
    }
}

/**
 * Helper to award a badge by name if the user doesn't already have it.
 */
function award_badge_if_not_exists($conn, $user_id, $badge_name) {
    // Get badge ID
    $badgeQuery = $conn->prepare("SELECT id FROM badges WHERE name = ?");
    $badgeQuery->bind_param("s", $badge_name);
    $badgeQuery->execute();
    $badgeRes = $badgeQuery->get_result();
    
    if ($badgeRes->num_rows === 1) {
        $badge_id = $badgeRes->fetch_assoc()['id'];
        
        // Insert into user_badges (INSERT IGNORE handles the UNIQUE constraint)
        $awardQuery = $conn->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $awardQuery->bind_param("ii", $user_id, $badge_id);
        $awardQuery->execute();
    }
}
