<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;

// use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns;
use Filament\Resources\Pages\Page;
use Visualbuilder\EmailTemplates\Components\Iframe;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class PreviewEmailTemplate extends Page
{
    use Concerns\HasRelationManagers;
    use Concerns\InteractsWithRecord;
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static string $resource = EmailTemplateResource::class;

    protected static string $view = 'filament-panels::resources.pages.view-record';

    public function getTitle(): string
    {
        return __('vb-email-templates::email-templates.general-labels.preview-email', ['label' => $this->record->name]);
    }

    protected $tokenHelper;

    public $iframe;
    public $src;

    public function __construct()
    {
        // parent::__construct();
        $this->tokenHelper = app(\Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface::class);
    }

    public function form(Form $form): Form
    {
        //View Email Template Form
        $emailTemplates = EmailTemplate::all()->pluck('name', 'id');

        return static::getResource()::form(
            $form
                ->operation('view')
                ->disabled()
                // ->model($this->getRecord())
                ->statePath($this->getFormStatePath())
                ->columns($this->hasInlineLabels() ? 1 : 2)
                ->inlineLabel($this->hasInlineLabels())
                ->schema(
                    [
                        Section::make()
                            ->schema(
                                [
                                    Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])
                                        ->schema(
                                            [
                                                Select::make('id')
                                                      ->options($emailTemplates)
                                                      ->searchable()
                                                      ->label(__('vb-email-templates::email-templates.general-labels.template-name'))
                                                      ->reactive()
                                                      ->afterStateUpdated(function ($state) {
                                                          $this->redirectRoute('filament.resources.email-templates.view', $state);
                                                      }),

                                                TextInput::make('from')
                                                         ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
                                                         ->disabled(),
                                            ]
                                        ),
                                    Grid::make(['default' => 1])
                                        ->schema(
                                            [
                                                TextInput::make('subject')
                                                         ->label(__('vb-email-templates::email-templates.form-fields-labels.subject'))
                                                         ->disabled(),
                                                TextInput::make('preheader')
                                                         ->label(__('vb-email-templates::email-templates.form-fields-labels.header'))
                                                         ->hint(__('vb-email-templates::email-templates.form-fields-labels.header-hint'))
                                                         ->disabled(),
                                            ]
                                        ),
                                    Grid::make(['default' => 1])
                                        ->schema(
                                            [
                                                Iframe::make('iframe'),
                                            ]
                                        ),

                                ]
                            ),
                    ]
                ),
        );
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    // public function form(Form $form): Form
    // {
    //     //View Email Template Form
    //     $emailTemplates = EmailTemplate::all()->pluck('name', 'id');

    //     return $form->schema(
    //         [
    //             Section::make()
    //                 ->schema(
    //                     [
    //                         Grid::make(['default' => 1, 'sm' => 1, 'md' => 2])
    //                             ->schema(
    //                                 [
    //                                     Select::make('id')
    //                                           ->options($emailTemplates)
    //                                           ->searchable()
    //                                           ->label(__('vb-email-templates::email-templates.general-labels.template-name'))
    //                                           ->reactive()
    //                                           ->afterStateUpdated(function ($state) {
    //                                               $this->redirectRoute('filament.resources.email-templates.view', $state);
    //                                           }),

    //                                     TextInput::make('from')
    //                                              ->label(__('vb-email-templates::email-templates.form-fields-labels.email-from'))
    //                                              ->disabled(),
    //                                 ]
    //                             ),
    //                         Grid::make(['default' => 1])
    //                             ->schema(
    //                                 [
    //                                     TextInput::make('subject')
    //                                              ->label(__('vb-email-templates::email-templates.form-fields-labels.subject'))
    //                                              ->disabled(),
    //                                     TextInput::make('preheader')
    //                                              ->label(__('vb-email-templates::email-templates.form-fields-labels.header'))
    //                                              ->hint(__('vb-email-templates::email-templates.form-fields-labels.header-hint'))
    //                                              ->disabled(),
    //                                 ]
    //                             ),
    //                         Grid::make(['default' => 1])
    //                             ->schema(
    //                                 [
    //                                     Iframe::make('iframe'),
    //                                 ]
    //                             ),

    //                     ]
    //                 ),
    //         ]
    //     );
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->tokenHelper->replaceTokens($value, $this);
        }

        return $data;
    }

    // protected function getForms(): array
    // {
    //     return [
    //         'form' => $this->makeForm()
    //                        ->operation('view')
    //                        ->model($this->getRecord())
    //                        ->schema($this->getFormSchema())
    //                        ->statePath('data')
    //                        ->inlineLabel(config('filament.layout.forms.have_inline_labels')),
    //     ];
    // }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    protected function fillForm(): void
    {
        $data = $this->getRecord()->attributesToArray();

        $this->form->fill($data);
    }

    protected function hasInfolist(): bool
    {
        return false;
    }
}