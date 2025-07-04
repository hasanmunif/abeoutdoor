<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\PhotosRelationManager;
// use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Katalog';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

                Forms\Components\TextArea::make('about')
                ->required()
                ->maxLength(1024),

                Forms\Components\TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('IDR')
                ->helperText('Harga per 3 hari sewa'),

                Forms\Components\TextInput::make('stock')
                ->required()
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->helperText('Jumlah stok tersedia'),

                // Forms\Components\Toggle::make('can_multi_quantity')
                // ->label('Multi Quantity?')
                // ->helperText('Apakah produk ini bisa disewa lebih dari 1 (seperti tenda, kompor, matras, dll)')
                // ->default(false),

                Forms\Components\FileUpload::make('thumbnail')
                ->required()
                ->image(),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('brand_id', null);
                    }),

                Forms\Components\Select::make('brand_id')
                    ->options(function (callable $get) {
                        $categoryId = $get('category_id');
                        if ($categoryId) {
                            return Brand::whereHas('brandCategories', function ($query) use ($categoryId) {
                                $query->where('category_id', $categoryId);
                            })->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\ImageColumn::make('thumbnail'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('brand.name'),
                Tables\Columns\TextColumn::make('price')
                ->money('IDR')
                ->label('Harga (3 hari)'),
                Tables\Columns\TextColumn::make('stock')
                ->sortable()
                ->label('Stok'),
                // Tables\Columns\IconColumn::make('can_multi_quantity')
                // ->boolean()
                // ->label('Multi Qty'),
            ])
            ->filters([
                //
                SelectFilter::make('category_id')
                ->label('Category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand_id')
                ->label('Brand')
                    ->relationship('brand', 'name'),
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
            PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}