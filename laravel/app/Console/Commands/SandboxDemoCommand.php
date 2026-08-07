<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AI\SandboxService;
use Illuminate\Console\Command;

/**
 * Démonstration du cycle de vie d'un sandbox Python (D14) en CLI :
 *
 *   1. open()   → workspace standard (input/ core/ reference/ output/ logs/)
 *   2. write()  → input/data.csv (données factices)
 *   3. write()  → core/main.py   (régression linéaire + graphique + PDF code-barres)
 *   4. run()    → exécution Python
 *   5. close()  → indexation des fichiers d'output/
 *
 * Usage : `php artisan sandbox:demo`
 */
class SandboxDemoCommand extends Command
{
    protected $signature = 'sandbox:demo';

    protected $description = "Prouve le cycle de vie d'un sandbox Python (open → write → run → close)";

    public function handle(SandboxService $sandbox): int
    {
        $user = User::where('email', 'test1@example.com')->first();

        $this->info('1) open() — création du workspace standard...');
        $sb = $sandbox->open($user, ['name' => 'Demo POC sandbox']);
        $this->line("   sandbox #{$sb->id} folder={$sb->folder}");

        $this->info('2) write() — input/data.csv (données factices)...');
        $csv = "feature,target\n"
            . "1,2.2\n2,3.9\n3,6.1\n4,7.8\n5,10.3\n6,12.4\n7,13.9\n8,16.1\n9,18.0\n10,19.6\n";
        $sandbox->write($sb, 'input', 'data.csv', $csv);

        $this->info('3) write() — core/main.py (régression + graphique + PDF)...');
        $code = <<<'PY'
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
import matplotlib.pyplot as plt
from reportlab.pdfgen import canvas
from barcode import EAN13
from barcode.writer import ImageWriter

df = pd.read_csv('input/data.csv')
X = df[['feature']].values
y = df['target'].values

model = LinearRegression().fit(X, y)
print('pente =', round(model.coef_[0], 4), '| intercept =', round(model.intercept_, 4))
print('R2 =', round(model.score(X, y), 4))

xs = np.linspace(X.min(), X.max(), 50).reshape(-1, 1)
pred = model.predict(xs)
plt.figure(figsize=(7, 5))
plt.scatter(X, y, alpha=0.6)
plt.plot(xs, pred, color='red')
plt.tight_layout()
plt.savefig('output/prediction.png', dpi=150)

items = [{'code': '3017620422003', 'libelle': 'Produit A'}, {'code': '3033710003312', 'libelle': 'Produit B'}]
pdf = canvas.Canvas('output/etiquettes.pdf')
y = 760
for it in items:
    EAN13(it['code'], writer=ImageWriter()).save('output/_bc_tmp', options={'module_height': 10.0})
    pdf.drawString(50, y, it['libelle'])
    pdf.drawImage('output/_bc_tmp.png', 50, y - 30, width=180, height=50)
    y -= 100
pdf.save()
print('PDF etiquettes généré')
PY;
        $sandbox->write($sb, 'core', 'main.py', $code);

        $this->info('4) run() — python core/main.py...');
        $result = $sandbox->run($sb, 'core/main.py');
        $this->line("   exit_code={$result['exit_code']}");
        foreach (explode("\n", trim($result['output'])) as $line) {
            $this->line("   | $line");
        }
        if ($result['error'] !== '') {
            $this->error($result['error']);
        }

        if ($result['exit_code'] !== 0) {
            $this->error('Échec de l\'exécution — sandbox en erreur.');

            return self::FAILURE;
        }

        $this->info('5) close() — indexation des fichiers d\'output/...');
        $files = $sandbox->close($sb);
        $this->line('   Fichiers produits :');
        foreach ($files as $file) {
            $this->line("   - {$file->name} ({$file->size} octets, {$file->mime})");
        }

        $this->info("Sandbox #{$sb->id} clôturé — statut : {$sb->status}");

        return self::SUCCESS;
    }
}
