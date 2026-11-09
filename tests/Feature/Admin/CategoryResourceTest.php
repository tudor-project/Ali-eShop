<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_a_category_with_translations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name'), 'web'));
        $this->actingAs($admin);

        $parent = Category::query()->create(['is_active' => true, 'sort_order' => 1]);
        CategoryTranslation::query()->create([
            'category_id' => $parent->id,
            'locale' => 'ru',
            'name' => 'Зеркала',
            'slug' => 'zerkala',
        ]);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'parent_id' => $parent->id,
                'is_active' => true,
                'sort_order' => 4,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'name' => 'Oglinzi de perete',
                        'slug' => 'oglinzi-de-perete',
                        'description' => 'Oglinzi pentru perete.',
                    ],
                    [
                        'locale' => 'ru',
                        'name' => 'Настенные зеркала',
                        'slug' => 'nastennye-zerkala',
                        'description' => 'Зеркала на стену.',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('sort_order', 4)->first();

        $this->assertNotNull($category);
        $this->assertSame($parent->id, $category->parent_id);
        $this->assertTrue($category->is_active);
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'locale' => 'ro',
            'name' => 'Oglinzi de perete',
            'slug' => 'oglinzi-de-perete',
            'description' => 'Oglinzi pentru perete.',
        ]);
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'locale' => 'ru',
            'slug' => 'nastennye-zerkala',
        ]);

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->assertFormSet([
                'parent_id' => $parent->id,
                'sort_order' => 4,
            ])
            ->fillForm([
                'is_active' => false,
                'sort_order' => 8,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'name' => 'Oglinzi mari',
                        'slug' => 'oglinzi-mari',
                        'description' => 'Descriere nouă',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertFalse($category->is_active);
        $this->assertSame(8, $category->sort_order);
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'locale' => 'ro',
            'name' => 'Oglinzi mari',
            'slug' => 'oglinzi-mari',
            'description' => 'Descriere nouă',
        ]);

        Livewire::test(ViewCategory::class, ['record' => $category->getKey()])
            ->assertSuccessful();

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords([$category])
            ->searchTable('Oglinzi')
            ->assertCanSeeTableRecords([$category]);

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->callAction(DeleteAction::class);

        $this->assertSoftDeleted($category);
    }

    public function test_category_slug_must_be_unique_per_locale_and_parent_cannot_be_itself(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name'), 'web'));
        $this->actingAs($admin);

        $existing = Category::query()->create(['is_active' => true, 'sort_order' => 0]);
        CategoryTranslation::query()->create([
            'category_id' => $existing->id,
            'locale' => 'ro',
            'name' => 'Oglinzi',
            'slug' => 'oglinzi',
        ]);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'is_active' => true,
                'sort_order' => 1,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'name' => 'Alta',
                        'slug' => 'oglinzi',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['translations.0.slug']);

        $this->assertSame(1, Category::query()->count());

        Livewire::test(EditCategory::class, ['record' => $existing->getKey()])
            ->fillForm([
                'parent_id' => $existing->id,
            ])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertNull($existing->refresh()->parent_id);
    }
}
