<?php

use App\Models\Exercise;

it('uses a representative image for each exercise type when no custom image is stored', function () {
    expect((new Exercise(['type' => 'musculation']))->resolvedImageUrl())->toContain('photo-1581009146145-b5ef050c2e1e');
    expect((new Exercise(['type' => 'cardio']))->resolvedImageUrl())->toContain('photo-1538805060514-97d9cc17730c');
    expect((new Exercise(['type' => 'mobilite']))->resolvedImageUrl())->toContain('photo-1506126613408-eca07ce68773');
});

it('keeps a custom exercise image when one is available', function () {
    $exercise = new Exercise(['type' => 'cardio', 'image_url' => 'https://cdn.example.test/custom-cardio.jpg']);

    expect($exercise->resolvedImageUrl())->toBe('https://cdn.example.test/custom-cardio.jpg');
});
