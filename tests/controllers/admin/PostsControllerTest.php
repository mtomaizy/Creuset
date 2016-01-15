<?php

namespace Creuset\Http\Controllers\Admin;

use Carbon\Carbon;
use Creuset\Media;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostsControllerTest extends \TestCase
{
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->logInAsAdmin();
    }

    public function testICanCreateAPost()
    {
        // I go to the create posts page
        $postTitle = 'Awesome Post Title';
        $postContent = 'Here is some post content';

        $this->visit('/admin/posts/create');

        $this->post('admin/posts', [
            'title'        => $postTitle,
            'slug'         => str_slug($postTitle),
            'content'      => $postContent,
            'published_at' => Carbon::now(),
            'user_id'      => $this->user->id,
            '_token'       => csrf_token(),
            ]);
        // And see that I created a post successfully
        $this->seeInDatabase('posts', [
            'title'   => $postTitle,
            'content' => $postContent,
            'type'    => 'post',
            'user_id' => $this->user->id,
            ]);
    }

    public function testICanEditAPost()
    {

        // And a post exists in the database
        $post = factory('Creuset\Post')->create();

        // I update the post
        $postTitle = 'Edited Title';

        $this->visit("/admin/posts/{$post->id}/edit")
             ->see('Edit Post');

        $this->patch("admin/posts/{$post->id}", [
            'title'  => $postTitle,
            'slug'   => str_slug($postTitle),
            '_token' => csrf_token(),
            ]);
        //dd($this->response->getContent());

        // And see that I edited the post successfully
        $this->seeInDatabase('posts', [
            'id'      => $post->id,
            'title'   => $postTitle,
            'type'    => 'post',
            ]);
    }

    /** @test **/
    public function it_trashes_a_post()
    {
        $post = factory('Creuset\Post')->create();

        $this->visit('/admin/posts')
             ->see($post->title);

        // move to trash
        $this->delete("/admin/posts/{$post->id}");
        $this->assertSessionHas('alert', 'Post moved to trash');
    }

    /** @test **/
    public function it_permanently_deletes_a_post()
    {
        $post = factory('Creuset\Post')->create([
            'deleted_at' => Carbon::now()->subDay(),
        ]);

        $this->visit('/admin/posts')
              ->dontSee($post->title);

        $this->visit('admin/posts/trash')
             ->see($post->title);

        // Delete permanently
        $this->delete("/admin/posts/{$post->id}");
        $this->assertSessionHas('alert', 'Post permanently deleted');

        $this->notSeeInDatabase('posts', [
            'title' => $post->title,
            ]);
    }

    /** @test **/
    public function it_restores_a_post()
    {
        $post = factory('Creuset\Post')->create();

        // move to trash
        $this->delete("/admin/posts/{$post->id}");
        // restore
        $this->put("/admin/posts/{$post->id}/restore");

        $this->visit('/admin/posts')
             ->see($post->title);
    }

    /** @test **/
    public function it_can_upload_an_image_to_a_post()
    {
        // Make a post
        $post = factory('Creuset\Post')->create();

        // And we need a file
        $faker = Factory::create();
        $image = $faker->image();
        $file = new UploadedFile($image, basename($image), null, null, null, true);

        // Send off the request to upload the file
        $response = $this->call('POST', "/admin/posts/{$post->id}/image", [], [], ['image' => $file]);

        // An Image instance (in JSON) should be returned
        $responseData = json_decode($response->getContent());

        // Ensure the image has been saved in the db and attached to our post
        $this->seeInDatabase('media', [
            'model_id'   => $post->id,
            'model_type' => 'Creuset\Post',
            'file_name'  => basename($image),
            ]);

        foreach ($post->getMedia() as $media_item) {
            $this->assertFileExists($media_item->getPath());
        }

        // Delete the post which should also delete its associated media
        $post->delete();
    }
}