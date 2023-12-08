<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Expense;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use OpenAI\Responses\Chat\CreateResponse;
use App\Filament\Resources\ExpenseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('photos')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        /**
                         * @var CreateResponse
                         */
                        // $response = OpenAI::chat()->create([
                        //     'model' => 'gpt-4-vision-preview',
                        //     'max_tokens' => 1200,
                        //     'messages' => [
                        //         [
                        //             'role' => 'system',
                        //             "content"=> "You are a helpful assistant help me dectect data from image. You're in this timezone ".config()->get('app.timezone')
                        //         ],
                        //         [
                        //             'role' => 'user', 
                        //             'content' => [ 
                        //                 [
                        //                     'type' => 'text',
                        //                     'text' => 'Do not include any explanations, only provide a  RFC8259 compliant JSON response  following this format without deviation.
                        //                     {title: string, total_amount: integer, description: text, paid_at: timestamp}'
                        //                 ],
                        //                 [
                        //                     'type' => 'image_url',
                        //                     'image_url' => [
                        //                         'url' => "data:image/jpeg;base64,".base64_encode(array_values($state)[0]->get())
                        //                     ]
                        //                 ]

                        //             ]
                        //         ],
                        //     ]
                        // ]);
                        // $content = Str::of($response->choices[0]->message->content)->trim("```json");
                        // Log::info($content);
                        // $data = json_decode(Str::of($response->choices[0]->message->content)->trim("```json"));
                        // $set('title', $data->title);
                        // $set('amount', $data->total_amount);
                        // $set('description', $data->description);
                        $set('paid_at', now()->subHour());
                    }),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TagsInput::make('tags'),
                Forms\Components\KeyValue::make('data'),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('VND')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
