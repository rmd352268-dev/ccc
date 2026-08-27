<!-- ========================================================= -->
<!-- 📢 OFFICIAL PUBLIC ANNOUNCEMENT POPUP MODAL               -->
<!-- ========================================================= -->
<div id="official-announcement-modal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.72); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px; opacity: 0; transition: opacity 0.25s ease;">
    <div id="announcement-card" style="background: #FFFFFF; color: #1E293B; width: 100%; max-width: 580px; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45); padding: 26px 28px 22px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; position: relative; transform: scale(0.95); transition: transform 0.25s ease;">
        
        <!-- Header / Greeting -->
        <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-bottom: 14px; letter-spacing: -0.01em;">
            Hello, users and friends.
        </div>

        <!-- Official Domains & Anti-Phishing Warning -->
        <div style="margin-bottom: 16px; font-size: 13.5px; line-height: 1.6;">
            <div>
                <strong style="color: #DC2626;">Only web:</strong>
                <a href="{{ url('/') }}" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: underline;">{{ request()->getHost() }}</a>
                • <span style="color: #2563EB; font-weight: 600;">payatecc.top</span>
                • <span style="color: #2563EB; font-weight: 600;">payatecc.click</span>
            </div>
            <div style="margin-top: 4px;">
                <strong style="color: #DC2626;">tor:</strong>
                <a href="http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion" target="_blank" style="color: #2563EB; font-weight: 700; word-break: break-all; text-decoration: underline;">
                    http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion
                </a>
            </div>
            <div style="color: #DC2626; font-weight: 800; font-size: 13px; margin-top: 4px;">
                are legitimate websites; all others are fake websites.
            </div>
        </div>

        <!-- Description Note -->
        <div style="font-size: 13px; color: #334155; line-height: 1.55; margin-bottom: 16px; font-weight: 500;">
            In order to provide users with more fresh CC, we continuously update our databases daily. If you need assistance, guides, or deposit support, you can contact our official public channels below:
        </div>

        <!-- Public Telegram Links (NO ADMIN BOT EXPOSED) -->
        <div style="font-size: 13px; color: #0F172A; line-height: 1.85; margin-bottom: 16px;">
            <div>
                <span style="font-weight: 700;">Payate Channel:</span>
                <a href="https://t.me/+d3_Sok-2bH9kMjI1" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: underline; margin-left: 4px;">
                    Channel
                </a>
            </div>
            <div>
                <span style="font-weight: 700;">Payate Group:</span>
                <a href="https://t.me/+d3_Sok-2bH9kMjI1" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: underline; margin-left: 4px;">
                    Group
                </a>
            </div>
            <div>
                <span style="font-weight: 700;">Live Support:</span>
                <a href="https://t.me/payate_desk_bot" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: underline; margin-left: 4px;">
                    @payate_desk_bot
                </a>
            </div>
        </div>

        <!-- Emoji Row -->
        <div style="font-size: 15px; letter-spacing: 4px; margin-bottom: 18px;">
            🚀 🚀 🚀 🚀 🚀 🚀
        </div>

        <!-- Bottom Action Bar with Green Confirm Button -->
        <div style="display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid #E2E8F0; padding-top: 14px;">
            <button type="button" id="btn-confirm-announcement" onclick="closeAnnouncementModal()" style="background: #16A34A; color: #FFFFFF; font-weight: 800; font-size: 13px; border: none; border-radius: 6px; padding: 8px 24px; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 2px 6px rgba(22,163,74,0.35);">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
function showAnnouncementModal() {
    const modal = document.getElementById('official-announcement-modal');
    const card = document.getElementById('announcement-card');
    if (!modal || !card) return;

    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        card.style.transform = 'scale(1)';
    }, 10);
}

function closeAnnouncementModal() {
    const modal = document.getElementById('official-announcement-modal');
    const card = document.getElementById('announcement-card');
    if (!modal || !card) return;

    modal.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 250);

    sessionStorage.setItem('announcement_seen_session', '1');
}

document.addEventListener('DOMContentLoaded', function() {
    // Show modal automatically on page load if not closed in this session
    if (!sessionStorage.getItem('announcement_seen_session')) {
        setTimeout(showAnnouncementModal, 300);
    }
});

// Close when clicking outside the card
document.getElementById('official-announcement-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAnnouncementModal();
    }
});
</script>
