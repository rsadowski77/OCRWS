<?php
function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function old(string $k): string { return isset($_POST[$k]) ? h((string)$_POST[$k]) : ''; }
function int_get(string $key): ?int {
    if (!isset($_GET[$key])) return null;
    $v = filter_var($_GET[$key], FILTER_VALIDATE_INT);
    return ($v === false) ? null : (int)$v;
}
