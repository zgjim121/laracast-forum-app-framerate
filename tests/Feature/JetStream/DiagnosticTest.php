<?php

use App\Models\User;
use Tests\TestUtilities;

uses(TestUtilities::class);

test('diagnostic test to verify file updates are being detected', function () {
    // This test should always pass - it's used to verify that file changes are detected
    expect(true)->toBeTrue();
    
    // Output test run time to verify this is a fresh run
    echo "\nDiagnostic test running at: " . date('Y-m-d H:i:s') . "\n";
});

