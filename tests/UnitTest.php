<?php

declare(strict_types=1);

use App\Core\HttpException;
use App\Core\ImageUploader;
use App\Core\Paginator;
use App\Core\Router;
use App\Core\ValidationError;
use App\Core\Validator;
use App\Services\OrderRules;

test('order rules allow only the intended transitions', function () {
    check(OrderRules::can('pending', 'preparing', 'manager'), 'manager accepts');
    check(OrderRules::can('pending', 'cancelled', 'customer'), 'customer cancels pending');
    check(OrderRules::can('preparing', 'cancelled', 'customer'), 'customer cancels while preparing');
    check(OrderRules::can('ready', 'out_for_delivery', 'rider'), 'rider picks up');
    check(!OrderRules::can('pending', 'delivered', 'admin'), 'cannot skip to delivered');
    check(!OrderRules::can('preparing', 'cancelled', 'manager'), 'manager cannot cancel after accepting');
    check(!OrderRules::can('ready', 'cancelled', 'customer'), 'customer cannot cancel a ready order');
    check(!OrderRules::can('delivered', 'cancelled', 'admin'), 'delivered is final');
    same(['preparing', 'cancelled'], OrderRules::options('pending', 'manager'));
    same([], OrderRules::options('cancelled', 'admin'));
});

test('validator reports the first error of each field', function () {
    $rules = ['email' => 'required|email', 'password' => 'required|min:8', 'again' => 'required|same:password', 'price' => 'price'];
    try {
        Validator::check(['email' => 'nope', 'password' => 'short', 'again' => 'other', 'price' => '-4'], $rules);
        check(false, 'should fail');
    } catch (ValidationError $error) {
        same(['email', 'password', 'again', 'price'], array_keys($error->errors()));
    }
    $clean = Validator::check(['email' => 'a@b.test', 'password' => 'long enough', 'again' => 'long enough', 'price' => '12.50'], $rules);
    same('12.50', $clean['price']);
});

test('router matches parameters and reports 404 and 405', function () {
    $router = new Router();
    $router->get('/items/{id}', ['X', 'show'], 'public');
    same(['id' => 7], $router->match('GET', '/items/7')['params']);
    throws(HttpException::class, fn () => $router->match('GET', '/items/abc'));
    try {
        $router->match('POST', '/items/7');
    } catch (HttpException $error) {
        same(405, $error->getCode());
    }
});

test('paginator keeps the page inside its range', function () {
    $page = new Paginator(25, 99, 10);
    same(3, $page->page);
    same(20, $page->offset());
    same(1, (new Paginator(0, -5, 10))->page);
});

test('image validation trusts file content, not the file name', function () {
    $uploader = new ImageUploader(Tests::$config['uploads']);
    $file = tempnam(sys_get_temp_dir(), 'fh');
    file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhWMjWwAAAABJRU5ErkJggg=='));
    same('png', $uploader->validate(['tmp_name' => $file, 'size' => filesize($file), 'error' => UPLOAD_ERR_OK, 'name' => 'x.txt']));
    file_put_contents($file, '<?php echo "not an image";');
    throws(ValidationError::class, fn () => $uploader->validate(['tmp_name' => $file, 'size' => 30, 'error' => UPLOAD_ERR_OK, 'name' => 'photo.jpg']));
    throws(ValidationError::class, fn () => $uploader->validate(['tmp_name' => $file, 'size' => 3 * 1024 * 1024, 'error' => UPLOAD_ERR_OK, 'name' => 'big.png']));
    unlink($file);
});
