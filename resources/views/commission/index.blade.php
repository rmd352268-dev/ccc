@extends('layouts.app')

@section('title', 'Affiliate & Commission Program')

@section('content')
<div style="margin-bottom: 24px;">
    <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-users-rays" style="color: #10B981;"></i> <span>Партнерская программа и комиссии (Affiliate Program)</span>
    </h2>
    <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 6px; line-height: 1.6;">
        Приглашайте новых пользователей по вашей реферальной ссылке и получайте <strong>высокую комиссию {{ number_format($cryptoSettings->referral_commission_percent ?? 50.00, 0) }}% от каждого пополнения</strong> ваших рефералов на пожизненной основе!
    </p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 26px;">
    <div class="filter-card" style="border-left: 4px solid #10B981;">
        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-family: monospace; font-weight: 800;">Доступная комиссия (Available)</span>
        <div style="font-size: 26px; font-weight: 900; color: #10B981; font-family: monospace; margin-top: 6px;">
            ${{ number_format($commissionBalance, 2) }}
        </div>
        <span style="font-size: 11px; color: var(--text-muted);">Готово к моментальному выводу</span>
    </div>

    <div class="filter-card" style="border-left: 4px solid #38BDF8;">
        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-family: monospace; font-weight: 800;">Всего заработано (Total Earned)</span>
        <div style="font-size: 26px; font-weight: 900; color: #38BDF8; font-family: monospace; margin-top: 6px;">
            ${{ number_format($totalEarned, 2) }}
        </div>
        <span style="font-size: 11px; color: var(--text-muted);">Общий заработок по рефералам</span>
    </div>

    <div class="filter-card" style="border-left: 4px solid #F59E0B;">
        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-family: monospace; font-weight: 800;">Приглашенные клиенты (Referrals)</span>
        <div style="font-size: 26px; font-weight: 900; color: #F59E0B; font-family: monospace; margin-top: 6px;">
            {{ $referredCount }} <span style="font-size: 14px; font-weight: 700; color: var(--text-secondary);">активных</span>
        </div>
        <span style="font-size: 11px; color: var(--text-muted);">Комиссионная ставка: {{ number_format($cryptoSettings->referral_commission_percent ?? 50.00, 0) }}%</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-bottom: 26px;">
    <!-- Referral Link & Rules Card -->
    <div class="filter-card">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-link" style="color: var(--gold-primary);"></i> Ваша персональная реферальная ссылка
        </h3>

        <div class="form-group">
            <label class="form-label">Реферальная ссылка для приглашений</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" readonly id="ref-link" class="form-control" value="{{ url('/register') }}?ref={{ $referralCode }}" style="font-family: monospace; font-weight: 700; color: var(--gold-primary);">
                <button type="button" class="btn-search" onclick="copyText(document.getElementById('ref-link').value, 'Реферальная ссылка скопирована!')">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
            <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">
                Ваш реферальный код: <strong style="color: var(--gold-primary); font-family: monospace;">{{ $referralCode }}</strong>
            </span>
        </div>

        <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; padding: 14px; margin-top: 18px;">
            <div style="font-size: 13px; font-weight: 800; color: #10B981; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-circle-check"></i> Условия партнерской программы {{ number_format($cryptoSettings->referral_commission_percent ?? 50.00, 0) }}%:
            </div>
            <ul style="font-size: 12px; color: var(--text-secondary); padding-left: 18px; line-height: 1.6; margin-bottom: 0;">
                <li>Отправьте вашу реферальную ссылку партнерам и друзьям.</li>
                <li>Каждый раз, когда приглашенный пользователь делает депозит, вам <strong>автоматически начисляется {{ number_format($cryptoSettings->referral_commission_percent ?? 50.00, 0) }}% от суммы пополнения</strong>.</li>
                <li>Заработанную комиссию можно моментально перевести на основной баланс в 1 клик!</li>
            </ul>
        </div>
    </div>

    <!-- Transfer Commission to Main Balance -->
    <div class="filter-card">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-money-bill-transfer" style="color: #10B981;"></i> Перевод комиссии на основной баланс
        </h3>

        <form action="{{ route('commission.transfer') }}" method="POST">
            @csrf
            <p style="font-size: 12.5px; color: var(--text-secondary); margin-bottom: 14px; line-height: 1.5;">
                Мгновенный перевод заработанной партнерской комиссии на ваш рабочий баланс для покупки карт.
            </p>

            <div class="form-group">
                <label class="form-label">Сумма к переводу ($ USD)</label>
                <input type="text" readonly class="form-control" value="${{ number_format($commissionBalance, 2) }}" style="font-family: monospace; font-size: 18px; font-weight: 900; color: #10B981;">
            </div>

            <button type="submit" class="btn-search" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;" {{ $commissionBalance <= 0 ? 'disabled' : '' }}>
                <i class="fa-solid fa-wallet"></i> Перевести ${{ number_format($commissionBalance, 2) }} на баланс
            </button>
        </form>
    </div>
</div>

<!-- Referral Commission History Table -->
<div class="table-card">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px; margin: 0;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--gold-primary);"></i> История начисления комиссий (Commission Log)
        </h3>
        <span style="font-size: 11px; font-weight: 800; color: #10B981; font-family: monospace;">
            {{ count($commissionHistory) }} ЗАПИСЕЙ
        </span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID транзакции</th>
                    <th>Реферал (Пользователь)</th>
                    <th>Сумма депозита</th>
                    <th>Ставка</th>
                    <th>Начисленная комиссия</th>
                    <th>Дата</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissionHistory as $c)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: var(--gold-primary);">
                            {{ $c->deposit_trx_id ?? 'COM-'.strtoupper(substr(md5($c->id), 0, 8)) }}
                        </td>
                        <td style="font-weight: 700; color: var(--text-primary);">
                            @ {{ $c->referred_username }}
                        </td>
                        <td style="font-family: monospace;">
                            ${{ number_format($c->deposit_amount, 2) }}
                        </td>
                        <td>
                            <span class="type-badge">{{ number_format($c->commission_rate, 0) }}%</span>
                        </td>
                        <td style="font-family: monospace; font-weight: 900; color: #10B981;">
                            +${{ number_format($c->commission_amount, 2) }}
                        </td>
                        <td style="font-size: 11px; color: var(--text-muted);">
                            {{ $c->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <span class="refundable-badge" style="color: #10B981; font-weight: 800;">Зачислено</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 28px; color: var(--text-muted);">
                            <i class="fa-solid fa-users" style="font-size: 24px; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                            Пока нет начислений по реферальной программе. Приглашайте новых пользователей по вашей ссылке!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
