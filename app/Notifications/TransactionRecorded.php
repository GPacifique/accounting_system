<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;

class TransactionRecorded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $transaction,
        public string $type,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $message = match($this->type) {
            'loan_payment' => "Loan payment of {$this->transaction->amount} recorded for loan #{$this->transaction->reference_id}",
            'loan_approval' => "Your loan request has been approved",
            'loan_disbursement' => "Your loan of {$this->transaction->amount} has been disbursed",
            'savings_deposit' => "Deposit of {$this->transaction->amount} recorded to your savings account",
            'savings_withdrawal' => "Withdrawal of {$this->transaction->amount} recorded from your savings account",
            'savings_interest' => "Interest of {$this->transaction->amount} added to your savings",
            default => "Transaction recorded: {$this->transaction->description}"
        };

        return new DatabaseMessage()
            ->line($message)
            ->action('View Details', route('transactions.show', $this->transaction));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->type) {
            'loan_payment' => 'Loan Payment Recorded',
            'loan_approval' => 'Loan Approved',
            'loan_disbursement' => 'Loan Disbursed',
            'savings_deposit' => 'Deposit Recorded',
            'savings_withdrawal' => 'Withdrawal Recorded',
            'savings_interest' => 'Interest Added',
            default => 'Transaction Recorded'
        };

        $actionText = 'View Transaction';
        $actionUrl = route('transactions.show', $this->transaction);

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line(match($this->type) {
                'loan_payment' => "A loan payment of {$this->transaction->amount} has been recorded for your loan.",
                'loan_approval' => "Your loan request has been approved!",
                'loan_disbursement' => "Your approved loan of {$this->transaction->amount} has been disbursed to your account.",
                'savings_deposit' => "A deposit of {$this->transaction->amount} has been recorded to your savings account.",
                'savings_withdrawal' => "A withdrawal of {$this->transaction->amount} has been processed from your savings account.",
                'savings_interest' => "Interest of {$this->transaction->amount} has been added to your savings account.",
                default => "A transaction has been recorded: {$this->transaction->description}"
            })
            ->action($actionText, $actionUrl)
            ->line('Thank you for using our system!');

        if ($this->transaction->notes) {
            $mailMessage->line('Notes: ' . $this->transaction->notes);
        }

        return $mailMessage;
    }
}
