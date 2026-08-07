'use client';

import { Icon } from '@/components/icons';
import type { Entity } from '@/lib/api/types';
import { sandboxFileDownloadUrl } from '@/features/ai-assistant/services/sandbox.service';

type SandboxFileRef = {
  name?: string;
  size?: number;
  mime?: string;
  sandbox_id?: number | string;
  file_id?: number | string;
};

/**
 * Affiche les fichiers produits par le sandbox Python (D14) joints à une
 * réponse de l'assistant. Les liens passent par le proxy Next (token en cookie).
 */
export function SandboxFiles({ files }: { files?: SandboxFileRef[] }) {
  if (!files || files.length === 0) return null;

  const valid = files.filter((f) => f.file_id && f.name);

  if (valid.length === 0) return null;

  return (
    <div className="mt-2 space-y-1 border-t border-border pt-2">
      <p className="text-xs font-medium text-muted-foreground">Fichiers produits :</p>
      {valid.map((f, i) => {
        const url = sandboxFileDownloadUrl(String(f.sandbox_id), String(f.file_id));
        return (
          <a
            key={`${String(f.file_id)}-${i}`}
            href={url}
            download
            className="flex items-center gap-2 rounded border border-border bg-background px-2 py-1.5 text-xs hover:border-primary"
          >
            <Icon name="download" className="h-3.5 w-3.5 shrink-0" />
            <span className="min-w-0 flex-1 truncate">{f.name}</span>
            {typeof f.size === 'number' && (
              <span className="shrink-0 text-muted-foreground">{formatBytes(f.size)}</span>
            )}
          </a>
        );
      })}
    </div>
  );
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} o`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} Ko`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
}
