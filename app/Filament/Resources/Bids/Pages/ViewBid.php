<?php

namespace App\Filament\Resources\Bids\Pages;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Filament\Resources\Bids\BidResource;
use App\Models\Bid;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBid extends ViewRecord
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->visible(fn (Bid $record): bool => method_exists($record, 'trashed') && $record->trashed()),
            DeleteAction::make()
                ->label('Void')
                ->hidden(fn (Bid $record): bool => (method_exists($record, 'trashed') && $record->trashed()) || (string) ($record->auction->status instanceof \BackedEnum ? $record->auction->status->value : $record->auction->status) === 'ENDED'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = parent::getBreadcrumbs();

        $record = $this->getRecord();

        if ($record && $record->auction) {
            $resourceName = static::$resource::getBreadcrumb();

            $newBreadcrumbs = [];

            foreach ($breadcrumbs as $url => $label) {
                if ($label === $resourceName) {
                    $auctionUrl = AuctionResource::getUrl('view', ['record' => $record->auction]);
                    $newBreadcrumbs[$auctionUrl] = $record->auction->title;
                }
                $newBreadcrumbs[$url] = $label;
            }

            return $newBreadcrumbs;
        }

        return $breadcrumbs;
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return 'Bid: ETB '.number_format($record->bid_amount, 2).' by '.$record->user->name;
    }
}
