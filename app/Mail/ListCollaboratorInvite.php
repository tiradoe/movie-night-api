<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\MovieList;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListCollaboratorInvite extends Mailable
{
    use Queueable, SerializesModels;

    public MovieList $movieList;

    public string $acceptUrl;

    public string $declineUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $list_owner,
        public Invitation $invitation,
    ) {
        $this->movieList = MovieList::find($invitation->movie_list_id);
        $this->acceptUrl = config('app.frontend_url').'/invitations/'.$invitation->token.'/accept';
        $this->declineUrl = config('app.frontend_url').'/invitations/'.$invitation->token.'/decline';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'List Collaborator Invite',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.list-collaborator-invite',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
