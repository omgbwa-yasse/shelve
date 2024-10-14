<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Statistics -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#statisticsMenu" aria-expanded="true" aria-controls="statisticsMenu" style="padding: 10px;">
            <i class="bi bi-bar-chart"></i> {{ __('statistics') }}
        </a>
        <div class="collapse show" id="statisticsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.mails') }}"><i class="bi bi-envelope"></i> {{ __('mail') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.repositories') }}"><i class="bi bi-journal-album"></i> {{ __('directory') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.communications') }}"><i class="bi bi-clipboard-check"></i> {{ __('request') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.transferrings') }}"><i class="bi bi-arrow-left-right"></i> {{ __('transfer') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.deposits') }}"><i class="bi bi-box-seam"></i> {{ __('deposit') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.tools') }}"><i class="bi bi-hammer"></i> {{ __('tool') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.dollies') }}"><i class="bi bi-truck"></i> {{ __('carts') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
