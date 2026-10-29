<?php
declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
use App\Env;
$file = tempnam(sys_get_temp_dir(), 'gateway-env-');
if ($file === false) throw new RuntimeException('Unable to create temporary test file');
try {
    file_put_contents($file, "# comment\nGATEWAY_TEST_A=hello\nGATEWAY_TEST_B=\"hello world\"\nGATEWAY_TEST_C=abc#def\nGATEWAY_TEST_D=plain # comment\n");
    putenv('GATEWAY_TEST_A=already-set');
    Env::load($file);
    $expected = ['GATEWAY_TEST_A'=>'already-set','GATEWAY_TEST_B'=>'hello world','GATEWAY_TEST_C'=>'abc#def','GATEWAY_TEST_D'=>'plain'];
    foreach ($expected as $key=>$value) {
        if (getenv($key) !== $value) throw new RuntimeException("FAIL: $key");
        echo "PASS: $key\n";
    }
    file_put_contents($file, 'INVALID LINE');
    try {
        Env::load($file);
        throw new RuntimeException('FAIL: invalid syntax was accepted');
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'FAIL: invalid syntax was accepted') throw $e;
        echo "PASS: invalid syntax rejected\n";
    }
} finally {
    unlink($file);
    foreach (['GATEWAY_TEST_A','GATEWAY_TEST_B','GATEWAY_TEST_C','GATEWAY_TEST_D'] as $key) putenv($key);
}
