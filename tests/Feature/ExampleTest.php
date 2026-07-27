<?php

test('a guest is directed to sign in from the application home page', function () {
    $this->get('/')->assertRedirect(route('login'));
});
