<?php
// Usage: include 'progressbar.php';
// Set $progressStep (1-5) before including this file
if (!isset($progressStep)) $progressStep = 1;
$steps = [
    ['label' => 'Psychometric Test', 'url' => '../psychometric_test/psychometric_test.php'],
    ['label' => 'Future Self', 'url' => '../future_self/futureself.php'],
    ['label' => 'Upload Image', 'url' => '../future_self/face_image.php'],
    ['label' => 'Create Avatar', 'url' => '../generate_avatar/avatar_frontpage.php'],
    ['label' => 'Start Chat', 'url' => '../chatbot/chatbot.php']
];
$progressPercent = (($progressStep-1) / (count($steps)-1)) * 100;
?>
<div class="progress-bar-container">
    <div class="progress-bar" style="width:<?php echo $progressPercent; ?>%"></div>
    <div class="progress-labels">
        <?php foreach ($steps as $i => $step): ?>
            <?php if ($progressStep-1 == $i): ?>
                <span class="progress-label active"><?php echo ($i+1) . '. ' . htmlspecialchars($step['label']); ?></span>
            <?php else: ?>
                <a href="<?php echo $step['url']; ?>" class="progress-label" style="text-decoration:none;">
                    <?php echo ($i+1) . '. ' . htmlspecialchars($step['label']); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
