<?php

namespace App\Filament\Resources\Auctions\Pages;

use App\Enums\AuctionStatus;
use App\Filament\Resources\Auctions\AuctionResource;
use App\Models\Auction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewAuction extends ViewRecord
{
    protected static string $resource = AuctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('This will make the auction visible to the public.')
                ->visible(function (Auction $record): bool {
                    return (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status) === AuctionStatus::DRAFT->value;
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::PUBLISHED,
                    ]);
                })
                ->successNotificationTitle('Auction published successfully'),

            Action::make('open_auction')
                ->label('Open Auction')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('This will open the auction for bidding.')
                ->visible(function (Auction $record): bool {
                    $status = (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status);

                    return $status === AuctionStatus::PUBLISHED->value;
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::LIVE,
                        'start_time' => now(),
                    ]);
                })
                ->successNotificationTitle('Auction opened successfully'),

            Action::make('close_now')
                ->label('Close Now')
                ->icon('heroicon-o-stop')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will immediately end the auction. This action cannot be undone.')
                ->visible(function (Auction $record): bool {
                    $status = (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status);

                    return in_array($status, [AuctionStatus::PUBLISHED->value, AuctionStatus::LIVE->value]);
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::ENDED,
                        'end_time' => now(),
                    ]);
                })
                ->successNotificationTitle('Auction closed successfully'),

            Action::make('mark_reserve_not_met')
                ->label('Reserve Not Met')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Mark this auction as having not met its reserve price.')
                ->visible(function (Auction $record): bool {
                    $status = (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status);
                    $reserve = $record->reserve_price !== null ? (float) $record->reserve_price : null;
                    $current = (float) $record->currentHighestBid();

                    return $status === AuctionStatus::ENDED->value
                        && $reserve !== null
                        && $current < $reserve
                        && $record->winner_bid_id === null;
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::ENDED,
                    ]);
                })
                ->successNotificationTitle('Marked as reserve not met'),

            Action::make('mark_payment_pending')
                ->label('Mark Payment Pending')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Mark this auction as awaiting payment from the winner.')
                ->visible(function (Auction $record): bool {
                    $status = (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status);

                    return $status === AuctionStatus::ENDED->value && $record->winner_bid_id !== null;
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::PAYMENT_PENDING,
                    ]);
                })
                ->successNotificationTitle('Marked as payment pending'),

            Action::make('mark_paid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Mark this auction as fully paid.')
                ->visible(function (Auction $record): bool {
                    $status = (string) ($record->status instanceof AuctionStatus ? $record->status->value : $record->status);

                    return $status === AuctionStatus::PAYMENT_PENDING->value;
                })
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::PAID,
                    ]);
                })
                ->successNotificationTitle('Marked as paid successfully'),
        ];
    }
}
