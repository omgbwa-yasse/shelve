<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email_account_id' => $this->email_account_id,
            'uid' => $this->uid,
            'folder' => $this->folder,
            'message_id' => $this->message_id,
            'in_reply_to' => $this->in_reply_to,
            'subject' => $this->subject,
            'from_address' => $this->from_address,
            'from_name' => $this->from_name,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            // Le corps HTML brut n'est JAMAIS rendu tel quel côté web (voir show.blade.php,
            // affiché dans un iframe sandboxé) — même prudence côté frontend Next.js : ne
            // jamais l'injecter en dangerouslySetInnerHTML, toujours via un iframe sandbox.
            'body_html' => $this->body_html,
            'body_text' => $this->body_text,
            'is_read' => (bool) $this->is_read,
            'is_flagged' => (bool) $this->is_flagged,
            'is_draft' => (bool) $this->is_draft,
            'is_answered' => (bool) $this->is_answered,
            'has_attachments' => (bool) $this->has_attachments,
            'sent_at' => $this->sent_at?->toIso8601ZuluString(),
            'tags' => EmailTagResource::collection($this->whenLoaded('tags')),
            'attachments' => EmailAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
