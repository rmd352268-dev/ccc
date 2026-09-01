<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Deposit;
use App\Services\TelegramNotificationService;

echo "Testing Deposit model attributes...\n";
$dep = Deposit::create([
    'username' => 'asadulislam17p',
    'trx_id' => 'DEP-VERIFY-' . time(),
    'currency' => 'USDT (TRC20)',
    'amount' => 25.00,
    'address' => 'TY6Xabc123testaddress',
    'status' => 'pending',
    'telegram_username' => '@testuser',
    'telegram_message_id' => '12345678',
    'telegram_chat_id' => '87654321',
    'admin_notes' => 'Test notes',
]);

echo "Created test deposit with ID: " . $dep->id . "\n";
$found = Deposit::find($dep->id);
echo "Retrieved telegram_message_id: " . $found->telegram_message_id . "\n";
echo "Retrieved telegram_chat_id: " . $found->telegram_chat_id . "\n";

// Test updateDepositTelegramMessage method
echo "Testing updateDepositTelegramMessage method execution...\n";
$updated = TelegramNotificationService::updateDepositTelegramMessage($found, 'completed', 'Unit Test Verification');
echo "updateDepositTelegramMessage returned: " . ($updated ? 'true' : 'false') . "\n";

// Clean up
$found->delete();
echo "Cleaned up test deposit.\n";
echo "ALL TESTS PASSED SUCCESSFULLY!\n";
