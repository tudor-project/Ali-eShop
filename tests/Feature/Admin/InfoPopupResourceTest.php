<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\InfoPopups\Pages\CreateInfoPopup;
use App\Filament\Resources\InfoPopups\Pages\EditInfoPopup;
use App\Filament\Resources\InfoPopups\Pages\ListInfoPopups;
use App\Filament\Resources\InfoPopups\Pages\ViewInfoPopup;
use App\Models\InfoPopup;
use App\Models\InfoPopupTranslation;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InfoPopupResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_an_info_popup_with_translations(): void
    {
        $this->actingAsAdmin();

        $this->assertFalse(Schema::hasColumn('info_popups', 'video_url'));
        $this->assertFalse(Schema::hasColumn('info_popup_translations', 'body'));
        $this->assertTrue(Schema::hasColumn('info_popup_translations', 'content'));

        Livewire::test(CreateInfoPopup::class)
            ->fillForm([
                'key' => 'size-help',
                'is_active' => true,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'title' => 'Cum alegi mărimea',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'data' => [
                                    'text' => '<p>Măsurați lățimea peretelui.</p><script>alert(1)</script><iframe src="https://evil.test"></iframe><img src="data:image/png;base64,aaaa">',
                                ],
                            ],
                            [
                                'type' => 'video',
                                'data' => [
                                    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                                ],
                            ],
                            [
                                'type' => 'list',
                                'data' => [
                                    'style' => 'ordered',
                                    'items' => [
                                        ['item' => 'Unu'],
                                        ['item' => 'Doi'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'locale' => 'ru',
                        'title' => 'Как выбрать размер',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'data' => [
                                    'text' => '<p>Измерьте ширину стены.</p>',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $popup = InfoPopup::query()->where('key', 'size-help')->first();

        $this->assertNotNull($popup);
        $this->assertTrue($popup->is_active);
        $romanian = $popup->translations()->where('locale', 'ro')->first();
        $this->assertNotNull($romanian);
        $this->assertSame(['paragraph', 'video', 'list'], array_column($romanian->content, 'type'));
        $this->assertStringContainsString('Măsurați lățimea peretelui.', $romanian->content[0]['data']['text']);
        $this->assertStringNotContainsString('script', strtolower($romanian->content[0]['data']['text']));
        $this->assertStringNotContainsString('evil.test', $romanian->content[0]['data']['text']);
        $this->assertStringNotContainsString('data:image', $romanian->content[0]['data']['text']);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $romanian->content[1]['data']['url']);
        $this->assertSame('ordered', $romanian->content[2]['data']['style']);
        $this->assertSame(['Unu', 'Doi'], $romanian->content[2]['data']['items']);
        $this->assertDatabaseHas('info_popup_translations', [
            'info_popup_id' => $popup->id,
            'locale' => 'ru',
            'title' => 'Как выбрать размер',
        ]);

        Livewire::test(EditInfoPopup::class, ['record' => $popup->getKey()])
            ->fillForm([
                'is_active' => false,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'title' => 'Ghid de mărimi',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'data' => [
                                    'text' => '<p>Text nou</p>',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $popup->refresh();
        $this->assertFalse($popup->is_active);
        $updated = $popup->translations()->where('locale', 'ro')->first();
        $this->assertNotNull($updated);
        $this->assertSame('Ghid de mărimi', $updated->title);
        $this->assertStringContainsString('Text nou', $updated->content[0]['data']['text']);

        Livewire::test(ViewInfoPopup::class, ['record' => $popup->getKey()])
            ->assertSuccessful();

        Livewire::test(ListInfoPopups::class)
            ->assertCanSeeTableRecords([$popup])
            ->searchTable('Ghid')
            ->assertCanSeeTableRecords([$popup]);

        Livewire::test(EditInfoPopup::class, ['record' => $popup->getKey()])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('info_popups', ['id' => $popup->id]);
        $this->assertDatabaseMissing('info_popup_translations', ['info_popup_id' => $popup->id]);
    }

    public function test_info_popup_key_must_be_unique(): void
    {
        $this->actingAsAdmin();

        $existing = InfoPopup::query()->create(['key' => 'install', 'is_active' => true]);
        InfoPopupTranslation::query()->create([
            'info_popup_id' => $existing->id,
            'locale' => 'ro',
            'title' => 'Instalare',
            'content' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => '<p>Pași de instalare.</p>'],
                ],
            ],
        ]);

        Livewire::test(CreateInfoPopup::class)
            ->fillForm([
                'key' => 'install',
                'is_active' => true,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'title' => 'Altul',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'data' => ['text' => '<p>Text</p>'],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);

        $this->assertSame(1, InfoPopup::query()->count());
    }

    public function test_info_popup_video_block_rejects_iframe_code(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateInfoPopup::class)
            ->fillForm([
                'key' => 'bad-video',
                'is_active' => true,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'title' => 'Video',
                        'content' => [
                            [
                                'type' => 'video',
                                'data' => [
                                    'url' => '<iframe src="https://evil.test/embed"></iframe>',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors();

        $this->assertSame(0, InfoPopup::query()->count());
    }

    public function test_local_video_is_stored_only_inside_the_popup_folder(): void
    {
        $popup = InfoPopup::query()->create(['key' => 'clip', 'is_active' => true]);

        $translation = InfoPopupTranslation::query()->create([
            'info_popup_id' => $popup->id,
            'locale' => 'ro',
            'title' => 'Montaj',
            'content' => [
                [
                    'type' => 'local_video',
                    'data' => ['path' => ['info-popups/montaj.mp4']],
                ],
                [
                    'type' => 'local_video',
                    'data' => ['path' => 'info-popups/../secret.mp4'],
                ],
            ],
        ]);

        $translation->refresh();

        $this->assertSame([
            [
                'type' => 'local_video',
                'data' => ['path' => 'info-popups/montaj.mp4'],
            ],
        ], $translation->content);
    }

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name'), 'web'));

        $permissions = [
            'ViewAny:InfoPopup',
            'View:InfoPopup',
            'Create:InfoPopup',
            'Update:InfoPopup',
            'Delete:InfoPopup',
            'DeleteAny:InfoPopup',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin->givePermissionTo($permissions);
        $this->actingAs($admin);
    }
}
