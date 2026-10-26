<?php
declare(strict_types=1);
function game_icon(string $name):string {
    $paths=[
        'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/>',
        'book'=>'<path d="M12 6C9 3 5 3 2 4v15c4-1 7-1 10 2 3-3 6-3 10-2V4c-3-1-7-1-10 2Zm0 0v15"/>',
        'bolt'=>'<path d="m13 2-9 12h7l-1 8 10-13h-7Z"/>',
        'trophy'=>'<path d="M7 3h10v6a5 5 0 0 1-10 0V3Zm0 2H3v3a4 4 0 0 0 4 4m10-7h4v3a4 4 0 0 1-4 4M12 14v6m-5 1h10"/>',
        'chart'=>'<path d="M4 3v18h17M8 16v-4m5 4V8m5 8V5"/>',
        'users'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 5v2"/>',
        'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>',
        'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'check'=>'<path d="m5 12 4 4L19 6"/>',
        'lock'=>'<rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>',
        'search'=>'<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/>',
        'flame'=>'<path d="M13 2c0 6-6 7-6 12a5 5 0 0 0 10 0c0-2-1-4-2-5 0 3-2 4-3 4 0-3 3-5 1-11Z"/>',
        'star'=>'<path d="m12 2 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1Z"/>',
        'shield'=>'<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6Z"/><path d="m8 12 3 3 5-6"/>',
        'chat'=>'<path d="M21 11a9 9 0 0 1-9 9H3l2-5a9 9 0 1 1 16-4Z"/><path d="M8 10h8m-8 4h5"/>',
        'crown'=>'<path d="m3 5 5 5 4-7 4 7 5-5-2 14H5ZM6 22h12"/>',
        'target'=>'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'globe'=>'<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>',
        'science'=>'<path d="M9 3h6m-5 0v7L4 20a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1l-6-10V3M7 16h10"/>',
        'math'=>'<path d="M4 6h7M7.5 2.5v7M15 6h6M4 16l6 6m0-6-6 6m11-5h6m-6 4h6"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['star']).'</svg>';
}

function game_nav_active(string $route,string $screen): bool
{
    $groups=[
        'student/dashboard.php'=>['dashboard'],
        'student/subjects.php'=>['subjects','units','lessons','lesson','quiz','activities','papers','paper'],
        'chatbot/chat.php'=>['chat','history','discuss'],
        'student/progress.php'=>['progress','leaderboard','results'],
        'student/leaderboard.php'=>['leaderboard'],
        'profile.php'=>['profile','parent_access','subscription'],
    ];
    return in_array($screen,$groups[$route]??[],true);
}
