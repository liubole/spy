## 简介
本工具是对 phptrace 的包装，需要先安装 phptrace[https://github.com/Qihoo360/phptrace]

## 用法
```php
Tricolor\Spy\Inspect::start();
// or
Tricolor\Spy\Inspect::start($output = '/tmp/zhang3.log', $cmd = 'phptrace -p %s');
```
