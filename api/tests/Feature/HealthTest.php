<?php

it('responde no endpoint de saúde', function () {
    $this->get('/up')->assertOk();
});
