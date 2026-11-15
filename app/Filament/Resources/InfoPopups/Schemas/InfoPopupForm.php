<?php

namespace App\Filament\Resources\InfoPopups\Schemas;

use App\Filament\Resources\InfoPopups\InfoPopupAdmin;
use App\Models\InfoPopup;
use App\Support\InfoPopupContent;
use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class InfoPopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::generalSection(),
                self::translationsSection(),
            ]);
    }

    private static function generalSection(): Section
    {
        return Section::make(__('admin.info_popups.sections.general'))
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                TextInput::make('key')
                    ->label(__('admin.info_popups.fields.key'))
                    ->helperText(__('admin.info_popups.hints.key'))
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->rule(fn (?InfoPopup $record) => Rule::unique('info_popups', 'key')->ignore($record)),
                Toggle::make('is_active')
                    ->label(__('admin.info_popups.fields.is_active'))
                    ->default(true),
            ]);
    }

    private static function translationsSection(): Section
    {
        return Section::make(__('admin.info_popups.sections.translations'))
            ->description(__('admin.info_popups.hints.translations'))
            ->columnSpanFull()
            ->schema([
                Repeater::make('translations')
                    ->relationship()
                    ->label(__('admin.info_popups.sections.translations'))
                    ->addActionLabel(__('admin.info_popups.actions.add_translation'))
                    ->minItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? ($state['locale'] ?? null))
                    ->columns(2)
                    ->schema([
                        Select::make('locale')
                            ->label(__('admin.info_popups.fields.locale'))
                            ->options(InfoPopupAdmin::locales())
                            ->required()
                            ->distinct()
                            ->native(false),
                        TextInput::make('title')
                            ->label(__('admin.info_popups.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        self::contentBuilder(),
                    ]),
            ]);
    }

    private static function contentBuilder(): Builder
    {
        return Builder::make('content')
            ->label(__('admin.info_popups.fields.content'))
            ->helperText(__('admin.info_popups.hints.content'))
            ->addActionLabel(__('admin.info_popups.actions.add_block'))
            ->reorderable()
            ->collapsible()
            ->minItems(1)
            ->columnSpanFull()
            ->blocks([
                Block::make('paragraph')
                    ->label(__('admin.info_popups.blocks.paragraph'))
                    ->schema([
                        RichEditor::make('text')
                            ->label(__('admin.info_popups.fields.paragraph'))
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachments(false)
                            ->linkProtocols(['http', 'https', 'mailto']),
                    ]),
                Block::make('image')
                    ->label(__('admin.info_popups.blocks.image'))
                    ->schema([
                        FileUpload::make('path')
                            ->label(__('admin.info_popups.fields.image'))
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/avif',
                            ])
                            ->disk('public')
                            ->directory('info-popups')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->required(),
                        TextInput::make('alt')
                            ->label(__('admin.info_popups.fields.image_alt'))
                            ->required()
                            ->maxLength(255),
                    ]),
                Block::make('video')
                    ->label(__('admin.info_popups.blocks.video'))
                    ->schema([
                        TextInput::make('url')
                            ->label(__('admin.info_popups.fields.video_url'))
                            ->helperText(__('admin.info_popups.hints.video_url'))
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (! is_string($value) || InfoPopupContent::embedUrl($value) === null) {
                                        $fail(__('admin.info_popups.validation.video_url'));
                                    }
                                },
                            ]),
                    ]),
                Block::make('local_video')
                    ->label(__('admin.info_popups.blocks.local_video'))
                    ->schema([
                        FileUpload::make('path')
                            ->label(__('admin.info_popups.fields.local_video'))
                            ->helperText(__('admin.info_popups.hints.local_video'))
                            ->acceptedFileTypes([
                                'video/mp4',
                                'video/webm',
                            ])
                            ->disk('public')
                            ->directory('info-popups')
                            ->visibility('public')
                            ->maxSize(51200)
                            ->required(),
                    ]),
                Block::make('list')
                    ->label(__('admin.info_popups.blocks.list'))
                    ->schema([
                        Select::make('style')
                            ->label(__('admin.info_popups.fields.list_style'))
                            ->options([
                                'unordered' => __('admin.info_popups.list_styles.unordered'),
                                'ordered' => __('admin.info_popups.list_styles.ordered'),
                            ])
                            ->default('unordered')
                            ->required()
                            ->native(false),
                        Repeater::make('items')
                            ->label(__('admin.info_popups.fields.list'))
                            ->addActionLabel(__('admin.info_popups.actions.add_list_item'))
                            ->simple(
                                TextInput::make('item')
                                    ->required()
                                    ->maxLength(500),
                            )
                            ->minItems(1)
                            ->reorderable()
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
