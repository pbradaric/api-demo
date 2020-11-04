<?php

namespace Tests\Feature;

use App\Classes\PictureHelper;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PicturesApiTest extends TestCase
{
    use RefreshDatabase;

    static $api_token = 'EO9fsHiGWxZPKyhHpMcr8sm1iW9omUs3O1BMrIisnQZ6qaGMjQ7zXvFAmbnc';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('teststorage');
        $this->seed();
    }

    public function testGetAllPicturesNoToken()
    {
        $response = $this->getJson('/api/pictures');
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
    }

    public function testGetAllPictures()
    {
        $response = $this->getJson('/api/pictures?api_token=' . self::$api_token);
        $response->assertStatus(200)
                 ->assertJson([
                     'type' => 'success'
                 ]);
    }

    public function testStorePicture()
    {
        $file = UploadedFile::fake()->image('picture01.jpg', 1000, 1000)->size(1024);
        $response = $this->postJson('/api/pictures?api_token=' . self::$api_token, [
            'file' => $file
        ]);
        $user = User::find(1);
        Log::debug($user);
        $response->assertStatus(200)
                 ->assertJson([
                     'type' => 'success'
                 ]);
        Storage::disk('teststorage')->assertExists(PictureHelper::getUserPictureStoragePath($user) . "/picture01.jpg");
    }

    public function testGetSpecificPicture()
    {
        $response = $this->getJson('/api/pictures/1?api_token=' . self::$api_token);
        $response->assertStatus(200)
            ->assertJson([
                'type' => 'success'
            ]);
    }

    public function testDeletePicture()
    {
        $response = $this->deleteJson('/api/pictures/1?api_token=' . self::$api_token);
        $user = User::find(1);
        $response->assertStatus(200)
                 ->assertJson([
                     'type' => 'success'
                 ]);
        Storage::disk('teststorage')->assertMissing(PictureHelper::getUserPictureStoragePath($user) . "/picture01.jpg");
    }
}
