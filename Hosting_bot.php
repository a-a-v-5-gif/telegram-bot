<?php
/*
===========================================
بوت استضافة الملفات المتكامل - Premium Host Bot
الإصدار: 2.5
المطور: @a_a_v_5
المميزات: خطط مدفوعة - دعم PHP/Python/HTML - ويب هوك تلقائي - دفع بالنجوم وTON
===========================================
*/

ob_start();

// ==========================================
// الإعدادات الأساسية
// ==========================================
$token = "8489921976:AAESepy5IJv3iyUDhctg1AqCtrYuhw1FHj8";
define("API_KEY", $token);
$admin = "7499318493";
$developer_username = "@a_a_v_5"; // يوزر المطور للتواصل
$domain = "ahmad_2011"; // الدومين المطلوب
$admin_email = "admin@ahmad2011.com"; // ايميل الإدارة

// ==========================================
// نظام الخطط المدفوعة
// ==========================================
$plans = [
    'free' => [
        'name' => '🆓 الخطة المجانية',
        'stars_price' => '0',
        'ton_price' => '0',
        'storage' => '100 MB',
        'files_limit' => 10,
        'file_types' => ['php', 'txt', 'json'],
        'features' => ['رفع ملفات PHP', 'رفع ملفات نصية', 'دعم أساسي'],
        'duration' => 'مستمر'
    ],
    'basic' => [
        'name' => '🥉 الخطة الأساسية',
        'stars_price' => '3,000',
        'ton_price' => '0.5',
        'storage' => '1 GB',
        'files_limit' => 50,
        'file_types' => ['php', 'py', 'html', 'txt', 'json'],
        'features' => ['جميع أنواع الملفات', 'دعم فني', 'نسخ احتياطي', '5 فولدرات'],
        'duration' => 'شهري'
    ],
    'premium' => [
        'name' => '🥇 الخطة المميزة',
        'stars_price' => '6,000',
        'ton_price' => '1',
        'storage' => '5 GB',
        'files_limit' => 200,
        'file_types' => ['php', 'py', 'html', 'js', 'css', 'txt', 'json'],
        'features' => ['جميع أنواع الملفات', 'دعم فني أولوية', 'نسخ احتياطي يومي', 'فولدرات غير محدودة', 'تحميل سريع'],
        'duration' => 'شهري'
    ],
    'vip' => [
        'name' => '👑 خطة VIP',
        'stars_price' => '12,000',
        'ton_price' => '2',
        'storage' => '20 GB',
        'files_limit' => 1000,
        'file_types' => ['php', 'py', 'html', 'js', 'css', 'sql', 'zip', 'txt', 'json'],
        'features' => ['كل شيء غير محدود', 'دعم فني على مدار الساعة', 'نسخ احتياطي فوري', 'أولوية في الخدمة', 'إحصائيات متقدمة'],
        'duration' => 'شهري'
    ]
];

// ==========================================
// دوال الاتصال بـ Telegram API
// ==========================================
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        error_log("CURL Error: " . curl_error($ch));
        return null;
    }
    curl_close($ch);
    return json_decode($res);
}

function callAPI($action, $channel_id, $user_id = null, $number = 1) {
    $api_url = 'https://abdomoh.serv00.net/api/eshterak_api.php'; 
    $data = ['action' => $action, 'channel_id' => $channel_id];
    
    if ($action === 'check' && $user_id !== null) {
        $data['user_id'] = $user_id;
    }
    if ($action === 'link') {
        $data['number'] = $number;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true) ?: ['error' => 'API Error'];
}

function send_message($message, $chat_id, $token) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'markdown'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// ==========================================
// دوال فحص الملفات والأمان
// ==========================================
function checkFileSafety($content, $file_type) {
    $dangerous_patterns = [
        '/eval\s*\(/i',
        '/base64_decode\s*\(/i',
        '/system\s*\(/i',
        '/shell_exec\s*\(/i',
        '/exec\s*\(/i',
        '/passthru\s*\(/i',
        '/proc_open\s*\(/i',
        '/popen\s*\(/i',
        '/phpinfo\s*\(/i',
        '/`.*`/',
        '/\$_GET\[.*\]/',
        '/\$_POST\[.*\]/',
        '/\$_REQUEST\[.*\]/',
        '/include\s*\(/i',
        '/require\s*\(/i',
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return false; // ملف خطير
        }
    }
    
    return true; // ملف آمن
}

function extractBotToken($content) {
    preg_match('/(\d{8,11}:[A-Za-z0-9_-]{35})/', $content, $matches);
    return $matches[0] ?? null;
}

function setupWebhookAuto($bot_token, $webhook_url) {
    $url = "https://api.telegram.org/bot$bot_token/setWebhook?url=" . urlencode($webhook_url);
    $result = @file_get_contents($url);
    
    if ($result === false) {
        return ['success' => false, 'error' => 'فشل الاتصال'];
    }
    
    $data = json_decode($result, true);
    return [
        'success' => $data['ok'] ?? false,
        'description' => $data['description'] ?? 'Unknown error'
    ];
}

// ==========================================
// تهيئة البيانات والمجلدات
// ==========================================
date_default_timezone_set('Africa/Cairo');

if (!file_exists('data')) mkdir('data', 0777, true);
if (!file_exists('uploads')) mkdir('uploads', 0777, true);
if (!file_exists('temp')) mkdir('temp', 0777, true);

$data_file = 'data/bot_data.json';
$users_file = 'data/users_data.json';
$stats_file = 'data/stats.json';

// تحميل البيانات
$bot_data = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
$users_data = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$stats = file_exists($stats_file) ? json_decode(file_get_contents($stats_file), true) : [];

// تهيئة البيانات إذا كانت فارغة
if (empty($bot_data)) {
    $bot_data = [
        'admins' => [$admin],
        'settings' => [
            'upload_enabled' => true,
            'auto_webhook' => true,
            'max_file_size' => 50 * 1024 * 1024, // 50MB
            'check_subscription' => true,
            'notify_admin' => true,
            'domain' => $domain,
            'admin_email' => $admin_email
        ],
        'statistics' => [
            'total_uploads' => 0,
            'total_users' => 0,
            'php_files' => 0,
            'py_files' => 0,
            'html_files' => 0,
            'blocked_files' => 0
        ]
    ];
}

// حفظ البيانات
function saveData() {
    global $bot_data, $users_data, $stats, $data_file, $users_file, $stats_file;
    file_put_contents($data_file, json_encode($bot_data, JSON_PRETTY_PRINT));
    file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT));
    file_put_contents($stats_file, json_encode($stats, JSON_PRETTY_PRINT));
}

// ==========================================
// معالجة طلبات Telegram
// ==========================================
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    echo "OK";
    exit;
}

// استخراج بيانات الرسالة
$message = $update['message'] ?? null;
$callback_query = $update['callback_query'] ?? null;

if ($callback_query) {
    $chat_id = $callback_query['message']['chat']['id'] ?? null;
    $message_id = $callback_query['message']['message_id'] ?? null;
    $from_id = $callback_query['from']['id'] ?? null;
    $data = $callback_query['data'] ?? null;
    $name = $callback_query['from']['first_name'] ?? 'مستخدم';
    $username = $callback_query['from']['username'] ?? null;
} elseif ($message) {
    $chat_id = $message['chat']['id'] ?? null;
    $message_id = $message['message_id'] ?? null;
    $from_id = $message['from']['id'] ?? null;
    $text = $message['text'] ?? null;
    $name = $message['from']['first_name'] ?? 'مستخدم';
    $username = $message['from']['username'] ?? null;
    $document = $message['document'] ?? null;
} else {
    exit;
}

// تهيئة بيانات المستخدم
if (!isset($users_data[$from_id])) {
    $users_data[$from_id] = [
        'plan' => 'free',
        'join_date' => date('Y-m-d H:i:s'),
        'expiry_date' => null,
        'uploaded_files' => 0,
        'used_storage' => 0,
        'files' => [],
        'warnings' => 0,
        'banned' => false
    ];
    saveData();
}

$user_data = $users_data[$from_id];
$user_plan = $user_data['plan'];
$current_plan = $plans[$user_plan];

// ==========================================
// معالجة الأوامر الأساسية
// ==========================================
if (isset($text)) {
    switch ($text) {
        case '/start':
            showMainMenu($chat_id, $message_id, $from_id, $name, $user_plan, $current_plan);
            break;
            
        case '/plans':
            showPlansMenu($chat_id, $message_id);
            break;
            
        case '/myplan':
            showMyPlan($chat_id, $message_id, $from_id, $user_data, $current_plan);
            break;
            
        case '/upload':
            if ($user_data['banned']) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "🚫 *حسابك محظور*\n\nللاستفسار عن سبب الحظر، راسل المطور: $developer_username",
                    'parse_mode' => 'markdown'
                ]);
                break;
            }
            
            if (!$bot_data['settings']['upload_enabled']) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "⏸️ *الرفع متوقف مؤقتاً*\n\nجاري الصيانة، يرجى المحاولة لاحقاً.",
                    'parse_mode' => 'markdown'
                ]);
                break;
            }
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "📤 *رفع ملف*\n\n" .
                         "ارسل لي الملف الذي تريد رفعه.\n" .
                         "📋 أنواع الملفات المسموحة في خطتك:\n" .
                         "• " . implode("\n• ", array_map(fn($ext) => ".$ext", $current_plan['file_types'])),
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📊 خطتي الحالية', 'callback_data' => 'my_plan']],
                        [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
                        [['text' => '🔙 رجوع', 'callback_data' => 'main_menu']]
                    ]
                ])
            ]);
            break;
            
        case '/stats':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminStats($chat_id, $message_id);
            } else {
                showUserStats($chat_id, $message_id, $from_id, $user_data);
            }
            break;
            
        case '/admin':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminPanel($chat_id, $message_id);
            }
            break;
            
        default:
            // معالجة النصوص الأخرى
            break;
    }
}

// ==========================================
// معالجة Callback Queries
// ==========================================
if (isset($data)) {
    $callback_id = $callback_query['id'];
    
    switch ($data) {
        case 'main_menu':
            showMainMenu($chat_id, $message_id, $from_id, $name, $user_plan, $current_plan);
            break;
            
        case 'plans':
            showPlansMenu($chat_id, $message_id);
            break;
            
        case 'my_plan':
            showMyPlan($chat_id, $message_id, $from_id, $user_data, $current_plan);
            break;
            
        case 'upgrade_plan':
            bot('answerCallbackQuery', [
                'callback_query_id' => $callback_id,
                'text' => "💰 اختر طريقة الدفع",
                'show_alert' => false
            ]);
            
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "💰 *ترقية الخطة*\n\n" .
                         "👤 المطور: $developer_username\n\n" .
                         "💳 *اختر طريقة الدفع:*",
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '⭐ الدفع بالنجوم', 'callback_data' => 'pay_stars']],
                        [['text' => '💎 الدفع بـ TON', 'callback_data' => 'pay_ton']],
                        [['text' => '❓ طريقة الدفع', 'callback_data' => 'how_to_pay']],
                        [['text' => '🔙 رجوع', 'callback_data' => 'plans']]
                    ]
                ])
            ]);
            break;
            
        case 'pay_stars':
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "⭐ *الدفع بالنجوم*\n\n" .
                         "💰 *الأسعار:*\n" .
                         "• 🥉 أساسية: 3,000 نجمة\n" .
                         "• 🥇 مميزة: 6,000 نجمة\n" .  
                         "• 👑 VIP: 12,000 نجمة\n\n" .
                         "📝 *خطوات الدفع:*\n" .
                         "1. اذهب إلى إعدادات Telegram\n" .
                         "2. اختر 'المال والمدفوعات'\n" .
                         "3. اختر 'تحويل نجوم'\n" .
                         "4. ارسل النجوم للمطور: $developer_username\n\n" .
                         "⚠️ *اكتب في الوصف:*\n" .
                         "`خطة - $from_id`\n\n" .
                         "📸 بعد التحويل:\n" .
                         "• صور إيصال التحويل\n" .
                         "• ارسله للمطور\n" .
                         "• انتظر تفعيل الخطة (5-10 دقائق)",
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📲 تحويل نجوم', 'url' => 'tg://resolve?domain=telegram&start=star-transfer']],
                        [['text' => '📨 ارسال ايصال', 'url' => "https://t.me/" . str_replace('@', '', $developer_username)]],
                        [['text' => '🔙 رجوع', 'callback_data' => 'upgrade_plan']]
                    ]
                ])
            ]);
            break;
            
        case 'pay_ton':
            bot('editMessageText', [
    'chat_id' => $chat_id,
    'message_id' => $message_id,
    'text' => "❓ *طرق الدفع المتاحة*\n\n" .
             "⭐ *النجوم:*\n" .
             "• طريقة سهلة ومباشرة\n" .
             "• متاحة لجميع المستخدمين\n" .
             "• تحويل فوري\n\n" .
             "💎 *عملة TON (عبر @wallet):*\n" .
             "1. افتح محفظة Telegram\n" .
             "2. اختر 'إرسال'\n" .
             "3. اكتب اسم المطور\n" .
             "4. ارسل المبلغ\n" .
             "5. اكتب الايدي في الوصف\n\n" .
             "📞 *تواصل مع المطور:*\n" .
             "$developer_username\n\n" .
             "🕒 *وقت التفعيل:*\n" .
             "• بعد التحقق من الدفع: 5-10 دقائق",
    'parse_mode' => 'markdown',
    'reply_markup' => json_encode([
        'inline_keyboard' => [
            [['text' => '⭐ الدفع بالنجوم', 'callback_data' => 'pay_stars']],
            [['text' => '💎 الدفع بـ TON', 'callback_data' => 'pay_ton']],
            [['text' => '🔙 رجوع', 'callback_data' => 'upgrade_plan']]
        ]
    ])
]);
break;
            
        case 'how_to_pay':
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "❓ *طرق الدفع المتاحة*\n\n" .
                         "⭐ *النجوم:*\n" .
                         "• طريقة سهلة ومباشرة\n" .
                         "• متاحة لجميع المستخدمين\n" .
                         "• تحويل فوري\n\n" .
                         "💎 *عملة TON:*\n" .
                         "• عملة رقمية مستقرة\n" .
                         "• تحويل دولي سريع\n" .
                         "• عمولات منخفضة\n\n" .
                         "📞 *تواصل مع المطور:*\n" .
                         "$developer_username\n\n" .
                         "🕒 *وقت التفعيل:*\n" .
                         "• بعد التحقق من الدفع: 5-10 دقائق\n" .
                         "• الدعم الفني: 24/7",
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '⭐ الدفع بالنجوم', 'callback_data' => 'pay_stars']],
                        [['text' => '💎 الدفع بـ TON', 'callback_data' => 'pay_ton']],
                        [['text' => '🔙 رجوع', 'callback_data' => 'upgrade_plan']]
                    ]
                ])
            ]);
            break;
            
        case 'copy_ton':
            bot('answerCallbackQuery', [
                'callback_query_id' => $callback_id,
                'text' => "✅ تم نسخ عنوان محفظة TON",
                'show_alert' => true
            ]);
            break;
            
        case str_starts_with($data, 'select_plan_'):
            $selected_plan = str_replace('select_plan_', '', $data);
            handlePlanSelection($chat_id, $message_id, $from_id, $selected_plan, $callback_id);
            break;
            
        case 'upload_file':
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "📤 *رفع ملف*\n\n" .
                         "ارسل لي الملف الذي تريد رفعه.\n" .
                         "📋 أنواع الملفات المسموحة في خطتك:\n" .
                         "• " . implode("\n• ", array_map(fn($ext) => ".$ext", $current_plan['file_types'])),
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📊 خطتي الحالية', 'callback_data' => 'my_plan']],
                        [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
                        [['text' => '🔙 رجوع', 'callback_data' => 'main_menu']]
                    ]
                ])
            ]);
            break;
            
        case 'my_files':
            showUserFiles($chat_id, $message_id, $from_id, $user_data);
            break;
            
        case 'support':
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "📞 *الدعم الفني*\n\n" .
                         "👤 المطور: $developer_username\n" .
                         "⏰ وقت الاستجابة: 24 ساعة\n\n" .
                         "للاستفسارات:\n" .
                         "• مشاكل في الرفع\n" .
                         "• استفسارات عن الخطط\n" .
                         "• مشاكل فنية\n" .
                         "• اقتراحات وتحسينات\n" .
                         "• مشاكل في الدفع",
                'parse_mode' => 'markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📞 تواصل مع المطور', 'url' => "https://t.me/" . str_replace('@', '', $developer_username)]],
                        [['text' => '💰 طرق الدفع', 'callback_data' => 'how_to_pay']],
                        [['text' => '🔙 رجوع', 'callback_data' => 'main_menu']]
                    ]
                ])
            ]);
            break;
            
        case str_starts_with($data, 'delete_file_'):
            $file_index = str_replace('delete_file_', '', $data);
            deleteUserFile($chat_id, $message_id, $from_id, $file_index, $callback_id);
            break;
            
        // ==========================================
        // قسم الإدارة (للمطورين فقط)
        // ==========================================
        case 'admin_panel':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminPanel($chat_id, $message_id);
            }
            break;
            
        case 'admin_stats':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminStats($chat_id, $message_id);
            }
            break;
            
        case 'admin_users':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminUsers($chat_id, $message_id);
            }
            break;
            
        case 'admin_settings':
            if (in_array($from_id, $bot_data['admins'])) {
                showAdminSettings($chat_id, $message_id);
            }
            break;
            
        case 'toggle_upload':
            if (in_array($from_id, $bot_data['admins'])) {
                $bot_data['settings']['upload_enabled'] = !$bot_data['settings']['upload_enabled'];
                saveData();
                
                $status = $bot_data['settings']['upload_enabled'] ? '✅ مفعل' : '❌ معطل';
                bot('answerCallbackQuery', [
                    'callback_query_id' => $callback_id,
                    'text' => "تم $status رفع الملفات",
                    'show_alert' => true
                ]);
                
                showAdminSettings($chat_id, $message_id);
            }
            break;
            
        case 'toggle_webhook':
            if (in_array($from_id, $bot_data['admins'])) {
                $bot_data['settings']['auto_webhook'] = !$bot_data['settings']['auto_webhook'];
                saveData();
                
                $status = $bot_data['settings']['auto_webhook'] ? '✅ مفعل' : '❌ معطل';
                bot('answerCallbackQuery', [
                    'callback_query_id' => $callback_id,
                    'text' => "تم $status الويب هوك التلقائي",
                    'show_alert' => true
                ]);
                
                showAdminSettings($chat_id, $message_id);
            }
            break;
            
        case 'broadcast':
            if (in_array($from_id, $bot_data['admins'])) {
                startBroadcast($chat_id, $message_id, $from_id);
            }
            break;
            
        case 'add_admin':
            if (in_array($from_id, $bot_data['admins'])) {
                addNewAdmin($chat_id, $message_id, $from_id);
            }
            break;
            
        default:
            // معالجة البيانات الأخرى
            break;
    }
}

// ==========================================
// معالجة رفع الملفات
// ==========================================
if (isset($document) && !$user_data['banned'] && $bot_data['settings']['upload_enabled']) {
    handleFileUpload($chat_id, $from_id, $document, $user_data, $current_plan);
}

// ==========================================
// الدوال المساعدة
// ==========================================
function showMainMenu($chat_id, $message_id, $user_id, $name, $user_plan, $current_plan) {
    global $bot_data, $users_data, $plans;
    
    $user_data = $users_data[$user_id] ?? [];
    $uploaded_files = $user_data['uploaded_files'] ?? 0;
    $files_limit = $current_plan['files_limit'];
    $progress_percent = min(100, round(($uploaded_files / $files_limit) * 100));
    
    $progress_bar = '';
    $bars = 10;
    $filled = round(($progress_percent / 100) * $bars);
    for ($i = 0; $i < $bars; $i++) {
        $progress_bar .= $i < $filled ? '▓' : '░';
    }
    
    $message = "👋 *مرحباً $name*\n\n";
    $message .= "📊 *حسابك الشخصي*\n";
    $message .= "🆔 الايدي: `$user_id`\n";
    $message .= "📋 الخطة: {$current_plan['name']}\n";
    $message .= "📁 الملفات: $uploaded_files من $files_limit\n";
    $message .= "📈 التقدم: $progress_bar $progress_percent%\n";
    $message .= "💾 المساحة: " . round($user_data['used_storage'] ?? 0, 2) . " MB\n\n";
    $message .= "⚡ *اختر من القائمة:*";
    
    $keyboard = [
        ['inline_keyboard' => [
            [['text' => '📤 رفع ملف', 'callback_data' => 'upload_file']],
            [['text' => '📁 ملفاتي', 'callback_data' => 'my_files']],
            [['text' => '📊 خطتي الحالية', 'callback_data' => 'my_plan']],
            [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
            [['text' => '📞 الدعم الفني', 'callback_data' => 'support']]
        ]]
    ];
    
    if (in_array($user_id, $bot_data['admins'])) {
        $keyboard[0]['inline_keyboard'][] = [['text' => '👑 لوحة التحكم', 'callback_data' => 'admin_panel']];
    }
    
    if ($message_id) {
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'markdown',
            'reply_markup' => json_encode($keyboard[0])
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'markdown',
            'reply_markup' => json_encode($keyboard[0])
        ]);
    }
}

function showPlansMenu($chat_id, $message_id) {
    global $plans, $developer_username;
    
    $message = "💰 *خطط الأسعار والاشتراكات*\n\n";
    $message .= "👤 يوزر المطور: $developer_username\n\n";
    
    foreach ($plans as $plan_id => $plan) {
        $message .= "*{$plan['name']}*\n";
        $message .= "💾 التخزين: {$plan['storage']}\n";
        $message .= "📁 الحد الأقصى: {$plan['files_limit']} ملف\n";
        $message .= "📄 الأنواع: " . implode(', ', $plan['file_types']) . "\n";
        
        if ($plan_id !== 'free') {
            $message .= "⭐ المميزات:\n";
            foreach ($plan['features'] as $feature) {
                $message .= "   • $feature\n";
            }
            $message .= "💰 السعر: {$plan['stars_price']} نجمة أو {$plan['ton_price']} TON\n";
        } else {
            $message .= "💰 السعر: مجاناً\n";
        }
        
        $message .= "\n";
    }
    
    $message .= "💫 *طرق الدفع المتاحة:*\n";
    $message .= "• ⭐ نجوم Telegram\n";
    $message .= "• 💎 عملة TON\n\n";
    $message .= "📞 للاشتراك: اختر '💰 ترقية الخطة'";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '🆓 الخطة المجانية', 'callback_data' => 'select_plan_free']],
            [['text' => '🥉 الخطة الأساسية', 'callback_data' => 'select_plan_basic']],
            [['text' => '🥇 الخطة المميزة', 'callback_data' => 'select_plan_premium']],
            [['text' => '👑 خطة VIP', 'callback_data' => 'select_plan_vip']],
            [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
            [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function showMyPlan($chat_id, $message_id, $user_id, $user_data, $current_plan) {
    global $plans;
    
    $uploaded_files = $user_data['uploaded_files'] ?? 0;
    $used_storage = $user_data['used_storage'] ?? 0;
    $files_limit = $current_plan['files_limit'];
    $storage_limit = filter_var($current_plan['storage'], FILTER_SANITIZE_NUMBER_INT);
    
    $files_percent = min(100, round(($uploaded_files / $files_limit) * 100));
    $storage_percent = min(100, round(($used_storage / $storage_limit) * 100));
    
    $message = "📊 *خطتك الحالية*\n\n";
    $message .= "*{$current_plan['name']}*\n";
    $message .= "📅 تاريخ الانضمام: {$user_data['join_date']}\n";
    
    if ($user_data['expiry_date']) {
        $message .= "⏰ تاريخ الانتهاء: {$user_data['expiry_date']}\n";
    }
    
    $message .= "\n📈 *إحصائيات الاستخدام*\n";
    $message .= "📁 الملفات: $uploaded_files من $files_limit ($files_percent%)\n";
    $message .= "💾 المساحة: " . round($used_storage, 2) . " MB من $storage_limit MB ($storage_percent%)\n";
    
    $message .= "\n📄 *الأنواع المسموحة:*\n";
    foreach ($current_plan['file_types'] as $type) {
        $message .= "• .$type\n";
    }
    
    if ($current_plan['name'] !== '🆓 الخطة المجانية') {
        $message .= "\n⭐ *المميزات:*\n";
        foreach ($current_plan['features'] as $feature) {
            $message .= "• $feature\n";
        }
    }
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
            [['text' => '📤 رفع ملف', 'callback_data' => 'upload_file']],
            [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function handlePlanSelection($chat_id, $message_id, $user_id, $selected_plan, $callback_id) {
    global $users_data, $plans, $developer_username, $bot_data;
    
    if ($selected_plan === 'free') {
        $users_data[$user_id]['plan'] = 'free';
        $users_data[$user_id]['expiry_date'] = null;
        saveData();
        
        bot('answerCallbackQuery', [
            'callback_query_id' => $callback_id,
            'text' => '✅ تم تفعيل الخطة المجانية بنجاح!',
            'show_alert' => true
        ]);
        
        showMyPlan($chat_id, $message_id, $user_id, $users_data[$user_id], $plans['free']);
    } else {
        $plan_info = $plans[$selected_plan];
        
        // إرسال طلب للمطور
        bot('sendMessage', [
            'chat_id' => $bot_data['admins'][0],
            'text' => "📋 *طلب اشتراك جديد*\n\n" .
                     "👤 المستخدم: $user_id\n" .
                     "📋 الخطة: {$plan_info['name']}\n" .
                     "⭐ السعر بالنجوم: {$plan_info['stars_price']} نجمة\n" .
                     "💎 السعر بـ TON: {$plan_info['ton_price']} TON\n\n" .
                     "📞 راسل المستخدم لتأكيد الدفع",
            'parse_mode' => 'markdown'
        ]);
        
        bot('answerCallbackQuery', [
            'callback_query_id' => $callback_id,
            'text' => "📞 تم إرسال طلبك للمطور $developer_username، اختر طريقة الدفع",
            'show_alert' => true
        ]);
        
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "📋 *طلب اشتراك مرسل*\n\n" .
                     "👤 المطور: $developer_username\n" .
                     "📋 الخطة: {$plan_info['name']}\n" .
                     "💰 الأسعار:\n" .
                     "• ⭐ بالنجوم: {$plan_info['stars_price']} نجمة\n" .
                     "• 💎 بـ TON: {$plan_info['ton_price']} TON\n\n" .
                     "💳 اختر طريقة الدفع المناسبة لك:",
            'parse_mode' => 'markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '⭐ الدفع بالنجوم', 'callback_data' => 'pay_stars']],
                    [['text' => '💎 الدفع بـ TON', 'callback_data' => 'pay_ton']],
                    [['text' => '🔙 رجوع', 'callback_data' => 'plans']]
                ]
            ])
        ]);
    }
}

// ==========================================
// الدوال الأخرى تبقى كما هي
// ==========================================
function handleFileUpload($chat_id, $user_id, $document, $user_data, $current_plan) {
    global $bot_data, $users_data, $stats, $domain;
    
    $file_id = $document['file_id'];
    $file_name = $document['file_name'];
    $file_size = $document['file_size'];
    $mime_type = $document['mime_type'] ?? '';
    
    // استخراج امتداد الملف
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // التحقق من أنواع الملفات المسموحة
    if (!in_array($file_ext, $current_plan['file_types'])) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ *خطأ في نوع الملف*\n\n" .
                     "نوع الملف `.$file_ext` غير مسموح به في خطتك.\n" .
                     "📋 الأنواع المسموحة: " . implode(', ', array_map(fn($ext) => ".$ext", $current_plan['file_types'])),
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    // التحقق من حجم الملف
    $max_file_size = $bot_data['settings']['max_file_size'];
    if ($file_size > $max_file_size) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ *حجم الملف كبير جداً*\n\n" .
                     "حجم الملف: " . round($file_size / 1024 / 1024, 2) . " MB\n" .
                     "الحد الأقصى: " . round($max_file_size / 1024 / 1024, 2) . " MB",
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    // التحقق من عدد الملفات
    if ($user_data['uploaded_files'] >= $current_plan['files_limit']) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ *تجاوزت الحد المسموح*\n\n" .
                     "عدد الملفات: {$user_data['uploaded_files']}\n" .
                     "الحد الأقصى: {$current_plan['files_limit']}\n\n" .
                     "💡 قم بترقية خطتك لرفع المزيد من الملفات.",
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    // تحميل الملف
    $file_info = bot('getFile', ['file_id' => $file_id]);
    if (!$file_info || !$file_info->ok) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ فشل في تحميل الملف",
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    $file_path = $file_info->result->file_path;
    $file_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;
    
    // تحميل المحتوى
    $file_content = file_get_contents($file_url);
    if ($file_content === false) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ فشل في تحميل محتوى الملف",
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    // فحص أمان الملف
    if (!checkFileSafety($file_content, $file_ext)) {
        $users_data[$user_id]['warnings']++;
        $bot_data['statistics']['blocked_files']++;
        
        if ($users_data[$user_id]['warnings'] >= 3) {
            $users_data[$user_id]['banned'] = true;
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "🚫 *تم حظر حسابك*\n\n" .
                         "تم اكتشاف محتوى خطير في الملفات المرفوعة.\n" .
                         "للاستفسار راسل المطور.",
                'parse_mode' => 'markdown'
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ *تحذير أمني*\n\n" .
                         "تم اكتشاف محتوى خطير في الملف.\n" .
                         "عدد التحذيرات: {$users_data[$user_id]['warnings']}/3",
                'parse_mode' => 'markdown'
            ]);
        }
        
        saveData();
        return;
    }
    
    // حفظ الملف
    $unique_id = uniqid();
    $new_file_name = $unique_id . '.' . $file_ext;
    $save_path = 'uploads/' . $new_file_name;
    
    if (file_put_contents($save_path, $file_content) === false) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ فشل في حفظ الملف",
            'parse_mode' => 'markdown'
        ]);
        return;
    }
    
    // تحديث بيانات المستخدم
    $users_data[$user_id]['uploaded_files']++;
    $users_data[$user_id]['used_storage'] += $file_size / 1024 / 1024; // تحويل إلى MB
    $users_data[$user_id]['files'][] = [
        'name' => $file_name,
        'saved_name' => $new_file_name,
        'size' => $file_size,
        'type' => $file_ext,
        'upload_date' => date('Y-m-d H:i:s'),
        'url' => $domain . '/uploads/' . $new_file_name
    ];
    
    // تحديث الإحصائيات العامة
    $bot_data['statistics']['total_uploads']++;
    switch ($file_ext) {
        case 'php':
            $bot_data['statistics']['php_files']++;
            break;
        case 'py':
            $bot_data['statistics']['py_files']++;
            break;
        case 'html':
            $bot_data['statistics']['html_files']++;
            break;
    }
    
    saveData();
    
    // إرسال رسالة نجاح
    $file_url_full = "https://" . $domain . "/uploads/" . $new_file_name;
    $message = "✅ *تم رفع الملف بنجاح*\n\n";
    $message .= "📄 *الاسم:* `$file_name`\n";
    $message .= "📏 *الحجم:* " . round($file_size / 1024, 2) . " KB\n";
    $message .= "📊 *النوع:* .$file_ext\n";
    $message .= "📅 *التاريخ:* " . date('Y-m-d H:i:s') . "\n";
    $message .= "🔗 *الرابط:* \n`$file_url_full`\n\n";
    $message .= "📈 *إحصائياتك:*\n";
    $message .= "• الملفات: {$users_data[$user_id]['uploaded_files']}/{$current_plan['files_limit']}\n";
    $message .= "• المساحة: " . round($users_data[$user_id]['used_storage'], 2) . " MB\n\n";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '🌐 فتح الرابط', 'url' => $file_url_full]],
            [['text' => '📋 ملفاتي', 'callback_data' => 'my_files']],
            [['text' => '📤 رفع ملف آخر', 'callback_data' => 'upload_file']],
            [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
        ]
    ];
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
    
    // إشعار المدير إذا كان مفعلاً
    if ($bot_data['settings']['notify_admin']) {
        bot('sendMessage', [
            'chat_id' => $bot_data['admins'][0],
            'text' => "📤 *ملف جديد مرفوع*\n\n" .
                     "👤 المستخدم: $user_id\n" .
                     "📄 الملف: $file_name\n" .
                     "📏 الحجم: " . round($file_size / 1024, 2) . " KB\n" .
                     "📊 النوع: .$file_ext\n" .
                     "📅 الوقت: " . date('Y-m-d H:i:s'),
            'parse_mode' => 'markdown'
        ]);
    }
}

function showUserFiles($chat_id, $message_id, $user_id, $user_data) {
    $files = $user_data['files'] ?? [];
    
    if (empty($files)) {
        $message = "📁 *ملفاتي*\n\n";
        $message .= "❌ لم تقم برفع أي ملفات بعد.\n\n";
        $message .= "📤 ارفع أول ملف عن طريق:\n";
        $message .= "1. اختر '📤 رفع ملف'\n";
        $message .= "2. ارسل الملف المطلوب\n";
        $message .= "3. احصل على الرابط مباشرة";
        
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📤 رفع ملف', 'callback_data' => 'upload_file']],
                [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
            ]
        ];
    } else {
        $message = "📁 *ملفاتي*\n\n";
        $message .= "📊 *إحصائيات:*\n";
        $message .= "• عدد الملفات: " . count($files) . "\n";
        $message .= "• إجمالي المساحة: " . round($user_data['used_storage'] ?? 0, 2) . " MB\n\n";
        $message .= "📄 *قائمة الملفات:*\n";
        
        $keyboard_buttons = [];
        
        foreach ($files as $index => $file) {
            $file_number = $index + 1;
            $message .= "$file_number. `{$file['name']}`\n";
            $message .= "   📏 " . round($file['size'] / 1024, 2) . " KB | 📅 {$file['upload_date']}\n";
            
            $keyboard_buttons[] = [
                ['text' => "🗑️ حذف الملف $file_number", 'callback_data' => 'delete_file_' . $index]
            ];
        }
        
        $keyboard_buttons[] = [
            ['text' => '📤 رفع ملف جديد', 'callback_data' => 'upload_file'],
            ['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']
        ];
        
        $keyboard = ['inline_keyboard' => $keyboard_buttons];
    }
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function deleteUserFile($chat_id, $message_id, $user_id, $file_index, $callback_id) {
    global $users_data, $bot_data;
    
    $file_index = intval($file_index);
    $user_files = $users_data[$user_id]['files'] ?? [];
    
    if (!isset($user_files[$file_index])) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $callback_id,
            'text' => '❌ الملف غير موجود',
            'show_alert' => true
        ]);
        return;
    }
    
    $file = $user_files[$file_index];
    $file_path = 'uploads/' . $file['saved_name'];
    
    // حذف الملف من السيرفر
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // تحديث بيانات المستخدم
    $users_data[$user_id]['uploaded_files']--;
    $users_data[$user_id]['used_storage'] -= $file['size'] / 1024 / 1024;
    if ($users_data[$user_id]['used_storage'] < 0) {
        $users_data[$user_id]['used_storage'] = 0;
    }
    
    // إزالة الملف من القائمة
    array_splice($users_data[$user_id]['files'], $file_index, 1);
    
    saveData();
    
    bot('answerCallbackQuery', [
        'callback_query_id' => $callback_id,
        'text' => '✅ تم حذف الملف بنجاح',
        'show_alert' => true
    ]);
    
    // تحديث القائمة
    showUserFiles($chat_id, $message_id, $user_id, $users_data[$user_id]);
}

function showAdminPanel($chat_id, $message_id) {
    global $bot_data;
    
    $settings = $bot_data['settings'];
    $stats = $bot_data['statistics'];
    
    $message = "👑 *لوحة التحكم - الإدارة*\n\n";
    $message .= "📊 *إحصائيات عامة:*\n";
    $message .= "• إجمالي الرفعات: {$stats['total_uploads']}\n";
    $message .= "• ملفات PHP: {$stats['php_files']}\n";
    $message .= "• ملفات Python: {$stats['py_files']}\n";
    $message .= "• ملفات HTML: {$stats['html_files']}\n";
    $message .= "• الملفات المحظورة: {$stats['blocked_files']}\n\n";
    
    $message .= "⚙️ *الإعدادات الحالية:*\n";
    $message .= "• رفع الملفات: " . ($settings['upload_enabled'] ? '✅ مفعل' : '❌ معطل') . "\n";
    $message .= "• الويب هوك التلقائي: " . ($settings['auto_webhook'] ? '✅ مفعل' : '❌ معطل') . "\n";
    $message .= "• الحد الأقصى للملف: " . round($settings['max_file_size'] / 1024 / 1024, 2) . " MB\n";
    $message .= "• التحقق من الاشتراك: " . ($settings['check_subscription'] ? '✅ مفعل' : '❌ معطل') . "\n";
    $message .= "• إشعارات المدير: " . ($settings['notify_admin'] ? '✅ مفعل' : '❌ معطل') . "\n";
    $message .= "• الدومين: {$settings['domain']}\n";
    $message .= "• ايميل الإدارة: {$settings['admin_email']}\n\n";
    
    $message .= "📋 *الأدوات الإدارية:*";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '📊 الإحصائيات المتقدمة', 'callback_data' => 'admin_stats']],
            [['text' => '👥 إدارة المستخدمين', 'callback_data' => 'admin_users']],
            [['text' => '⚙️ الإعدادات', 'callback_data' => 'admin_settings']],
            [['text' => '📢 بث رسالة', 'callback_data' => 'broadcast']],
            [['text' => '➕ إضافة مشرف', 'callback_data' => 'add_admin']],
            [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function showAdminStats($chat_id, $message_id) {
    global $bot_data, $users_data, $stats;
    
    $total_users = count($users_data);
    $active_users = 0;
    $banned_users = 0;
    $free_users = 0;
    $paid_users = 0;
    $total_storage = 0;
    
    foreach ($users_data as $user) {
        if ($user['banned']) {
            $banned_users++;
        } else {
            $active_users++;
        }
        
        if ($user['plan'] === 'free') {
            $free_users++;
        } else {
            $paid_users++;
        }
        
        $total_storage += $user['used_storage'] ?? 0;
    }
    
    $message = "📊 *الإحصائيات المتقدمة*\n\n";
    $message .= "👥 *المستخدمين:*\n";
    $message .= "• الإجمالي: $total_users\n";
    $message .= "• النشطين: $active_users\n";
    $message .= "• المحظورين: $banned_users\n";
    $message .= "• مجانيين: $free_users\n";
    $message .= "• مدفوعين: $paid_users\n\n";
    
    $message .= "💾 *التخزين:*\n";
    $message .= "• إجمالي المساحة المستخدمة: " . round($total_storage, 2) . " MB\n\n";
    
    $message .= "📁 *الملفات:*\n";
    $message .= "• إجمالي المرفوعات: {$stats['total_uploads']}\n";
    $message .= "• PHP: {$stats['php_files']}\n";
    $message .= "• Python: {$stats['py_files']}\n";
    $message .= "• HTML: {$stats['html_files']}\n";
    $message .= "• محظورة: {$stats['blocked_files']}\n\n";
    
    $message .= "📅 *آخر تحديث:* " . date('Y-m-d H:i:s');
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '🔄 تحديث الإحصائيات', 'callback_data' => 'admin_stats']],
            [['text' => '🔙 لوحة التحكم', 'callback_data' => 'admin_panel']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function showAdminSettings($chat_id, $message_id) {
    global $bot_data;
    
    $settings = $bot_data['settings'];
    
    $message = "⚙️ *إعدادات النظام*\n\n";
    $message .= "🔧 *التحكم في الميزات:*";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => ($settings['upload_enabled'] ? '✅' : '❌') . ' رفع الملفات', 'callback_data' => 'toggle_upload']],
            [['text' => ($settings['auto_webhook'] ? '✅' : '❌') . ' الويب هوك التلقائي', 'callback_data' => 'toggle_webhook']],
            [['text' => ($settings['check_subscription'] ? '✅' : '❌') . ' التحقق من الاشتراك', 'callback_data' => 'toggle_subscription']],
            [['text' => ($settings['notify_admin'] ? '✅' : '❌') . ' إشعارات المدير', 'callback_data' => 'toggle_notify']],
            [['text' => '🔙 لوحة التحكم', 'callback_data' => 'admin_panel']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

function startBroadcast($chat_id, $message_id, $from_id) {
    // حفظ حالة البث
    $bot_data['broadcast_mode'] = true;
    $bot_data['broadcast_user'] = $from_id;
    saveData();
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "📢 *وضع البث*\n\n" .
                 "ارسل الآن الرسالة التي تريد بثها لجميع المستخدمين.\n\n" .
                 "⚠️ *ملاحظات:*\n" .
                 "• يمكنك استخدام Markdown\n" .
                 "• تأكد من صحة الرسالة\n" .
                 "• البث قد يستغرق وقتاً\n\n" .
                 "❌ لإلغاء البث: ارسل /cancel",
        'parse_mode' => 'markdown'
    ]);
}

function addNewAdmin($chat_id, $message_id, $from_id) {
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "➕ *إضافة مشرف جديد*\n\n" .
                 "ارسل الآن ايدي المستخدم الذي تريد إضافته كمشرف.\n\n" .
                 "📝 *طريقة الحصول على الايدي:*\n" .
                 "1. تأكد أن المستخدم تفاعل مع البوت\n" .
                 "2. يمكنك استخدام /stats لرؤية ايدي المستخدمين\n\n" .
                 "❌ للإلغاء: ارسل /cancel",
        'parse_mode' => 'markdown'
    ]);
}

function showUserStats($chat_id, $message_id, $user_id, $user_data) {
    global $bot_data, $users_data;
    
    $total_users = count($users_data);
    $user_rank = 1;
    
    // حساب ترتيب المستخدم حسب عدد الملفات
    $sorted_users = [];
    foreach ($users_data as $id => $data) {
        $sorted_users[$id] = $data['uploaded_files'] ?? 0;
    }
    arsort($sorted_users);
    
    $rank = 1;
    foreach ($sorted_users as $id => $files) {
        if ($id == $user_id) {
            $user_rank = $rank;
            break;
        }
        $rank++;
    }
    
    $message = "📊 *إحصائياتك الشخصية*\n\n";
    $message .= "👤 *معلومات الحساب:*\n";
    $message .= "• الايدي: `$user_id`\n";
    $message .= "• الترتيب العام: #$user_rank من $total_users\n";
    $message .= "• تاريخ الانضمام: {$user_data['join_date']}\n\n";
    
    $message .= "📁 *الملفات:*\n";
    $message .= "• الملفات المرفوعة: {$user_data['uploaded_files']}\n";
    $message .= "• المساحة المستخدمة: " . round($user_data['used_storage'] ?? 0, 2) . " MB\n";
    $message .= "• عدد التحذيرات: {$user_data['warnings']}\n";
    $message .= "• الحالة: " . ($user_data['banned'] ? '🚫 محظور' : '✅ نشط') . "\n\n";
    
    $message .= "💡 *نصائح:*\n";
    $message .= "• حافظ على أمان ملفاتك\n";
    $message .= "• قم بترقية خطتك للمزيد من المميزات\n";
    $message .= "• تواصل مع الدعم الفني لأي استفسار";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '📊 خطتي الحالية', 'callback_data' => 'my_plan']],
            [['text' => '💰 ترقية الخطة', 'callback_data' => 'upgrade_plan']],
            [['text' => '🔙 القائمة الرئيسية', 'callback_data' => 'main_menu']]
        ]
    ];
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'markdown',
        'reply_markup' => json_encode($keyboard)
    ]);
}

// ==========================================
// معالجة النصوص الأخرى (للإدارة)
// ==========================================
if (isset($text) && in_array($from_id, $bot_data['admins'])) {
    // معالجة البث
    if (isset($bot_data['broadcast_mode']) && $bot_data['broadcast_mode'] && $bot_data['broadcast_user'] == $from_id) {
        if ($text === '/cancel') {
            unset($bot_data['broadcast_mode'], $bot_data['broadcast_user']);
            saveData();
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "❌ تم إلغاء عملية البث",
                'parse_mode' => 'markdown'
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⏳ *جاري إرسال البث...*\n\n" .
                         "الرسالة: $text\n\n" .
                         "يرجى الانتظار...",
                'parse_mode' => 'markdown'
            ]);
            
            $sent = 0;
            $failed = 0;
            $total = count($users_data);
            
            foreach ($users_data as $user_id => $user_data) {
                if (!$user_data['banned']) {
                    $result = bot('sendMessage', [
                        'chat_id' => $user_id,
                        'text' => "📢 *إشعار من الإدارة*\n\n" . $text,
                        'parse_mode' => 'markdown'
                    ]);
                    
                    if ($result && $result->ok) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                    
                    // تأخير لتجنب حظر التلغرام
                    usleep(50000); // 0.05 ثانية
                }
            }
            
            unset($bot_data['broadcast_mode'], $bot_data['broadcast_user']);
            saveData();
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ *تم الانتهاء من البث*\n\n" .
                         "📊 النتائج:\n" .
                         "• الإجمالي: $total مستخدم\n" .
                         "• تم الإرسال: $sent\n" .
                         "• فشل الإرسال: $failed\n" .
                         "• تم تجاوز المحظورين",
                'parse_mode' => 'markdown'
            ]);
        }
    }
    
    // معالجة إضافة مشرف
    if ($text && is_numeric($text) && strlen($text) > 5) {
        if (!in_array($text, $bot_data['admins'])) {
            $bot_data['admins'][] = $text;
            saveData();
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ تم إضافة المستخدم $text كمشرف بنجاح",
                'parse_mode' => 'markdown'
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ المستخدم $text مشرف بالفعل",
                'parse_mode' => 'markdown'
            ]);
        }
    }
}

// ==========================================
// حفظ البيانات في النهاية
// ==========================================
saveData();

// ==========================================
// نهاية الملف
// ==========================================
echo "OK";
?>
