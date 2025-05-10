<?php

use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->validData = fn() => [
        'title' => 'hello worlds',
        'topic_id' => Topic::factory()->create()->getKey(),
        'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        Etiam at odio id mi luctus volutpat ac at velit. Ut sit amet aliquet sapien.
        Duis a est imperdiet, placerat tellus id, laoreet erat. Mauris et mi aliquam,
        imperdiet purus ut, congue augue. Fusce eget tristique nisi. Ut molestie
        tempus dolor vel scelerisque. Sed at nisi eu ex sagittis fermentum'
    ];
});

it('requires authentication', function () {
    post(route('posts.store'))->assertRedirect(route('login'));
});

it('stores a post', function () {
    $user = User::factory()->create();

    $data = value($this->validData);

    actingAs($user)->post(route('posts.store'), $data);

    $this->assertDatabaseHas(Post::class, [
        ...$data,
        'user_id' => $user->id,
    ]);
});

it('redirects to the post show page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('posts.store'), value($this->validData))
        ->assertRedirect(Post::latest('id')->first()->showRoute());


});

it('requires valid data', function (array $badData, array|string $errors) {
    actingAs(User::factory()->create())
        ->post(route('posts.store'), [...value($this->validData), ...$badData])
        ->assertInvalid($errors);
})->with([
    [['title' => null], 'title'],
    [['title' => true], 'title'],
    [['title' => 1], 'title'],
    [['title' => 1.5], 'title'],
    [['title' => str_repeat('a', 121)], 'title'],
    [['title' => str_repeat('a', 9)], 'title'],
    [['topic_id' => null], 'topic_id'],
    [['topic_id' => -1 ], 'topic_id'],
    [['body' => null], 'body'],
    [['body' => true], 'body'],
    [['body' => 1], 'body'],
    [['body' => 1.5], 'body'],
    [['body' => str_repeat('a', 10_001)], 'body'],
    [['body' => str_repeat('a', 99)], 'body'],
]);
