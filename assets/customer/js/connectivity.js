function hypwaConnectivityNotice() {
    const notice = document.createElement( 'div' );
    notice.id = 'hypwa-connectivity-notice';
    notice.setAttribute( 'role', 'status' );
    notice.setAttribute( 'aria-live', 'polite' );

    notice.innerHTML =
        '<div class="hypwa-connectivity-icon" aria-hidden="true">' +
            '<svg id="hypwa-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">' +
                '<path id="hypwa-icon-path" d=""></path>' +
            '</svg>' +
            '<span id="hypwa-icon-span" class="dashicons" style="display: none;"></span>' +
        '</div>' +
        '<div class="hypwa-connectivity-body">' +
            '<strong class="hypwa-connectivity-title"></strong>' +
            '<div class="hypwa-connectivity-description"></div>' +
        '</div>' +
        '<div class="hypwa-connectivity-progress">' +
            '<div class="hypwa-connectivity-progress-bar"></div>' +
        '</div>';

    document.body.appendChild( notice );

    const iconSvg  = notice.querySelector( '#hypwa-icon-svg' );
    const iconPath = notice.querySelector( '#hypwa-icon-path' );
    const iconSpan = notice.querySelector( '#hypwa-icon-span' );
    const titleEl  = notice.querySelector( '.hypwa-connectivity-title' );
    const descEl   = notice.querySelector( '.hypwa-connectivity-description' );
    const barEl    = notice.querySelector( '.hypwa-connectivity-progress-bar' );

    let hideTimeout = null;

    const resetBar = () => {
        barEl.classList.remove( 'hypwa-animating' );
        void barEl.offsetWidth;
        barEl.classList.add( 'hypwa-animating' );
    };

    const showNotice = ( { title, description, bgColor, textColor, icon, isOnline } ) => {
        clearTimeout( hideTimeout );

        notice.style.setProperty( '--hypwa-connectivity-bg',    bgColor );
        notice.style.setProperty( '--hypwa-connectivity-color', textColor );

        titleEl.textContent = title;
        descEl.textContent  = description;

        const isWifi = (icon === 'dashicons-wifi');
        const isWifiAlt = (icon === 'dashicons-wifi-alt2');

        if ( isWifi || isWifiAlt ) {
            iconSpan.style.display = 'none';
            iconSvg.style.display = 'block';
            const pathD = isWifi 
                ? 'M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01'
                : 'M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20';
            iconPath.setAttribute( 'd', pathD );
        } else {
            iconSvg.style.display = 'none';
            iconSpan.style.display = 'block';
            iconSpan.className = 'dashicons ' + icon;
        }

        notice.classList.toggle( 'hypwa-is-online', isOnline );
        notice.classList.add( 'hypwa-is-visible' );

        resetBar();

        hideTimeout = setTimeout( () => {
            notice.classList.remove( 'hypwa-is-visible' );
        }, 2500 );
    };

    const showOffline = () => {
        showNotice( {
            title       : hypwa_sw.conn_notice_title,
            description : hypwa_sw.conn_notice_description,
            bgColor     : hypwa_sw.conn_notice_bg_color,
            textColor   : hypwa_sw.conn_notice_text_color,
            icon        : hypwa_sw.conn_notice_icon || 'dashicons-wifi',
            isOnline    : false,
        } );
    };

    const showOnline = () => {
        showNotice( {
            title       : hypwa_sw.conn_online_notice_title,
            description : hypwa_sw.conn_online_notice_description,
            bgColor     : hypwa_sw.conn_online_notice_bg_color,
            textColor   : hypwa_sw.conn_online_notice_text_color,
            icon        : hypwa_sw.conn_online_icon || 'dashicons-wifi',
            isOnline    : true,
        } );
    };

    window.addEventListener( 'offline', showOffline );
    window.addEventListener( 'online',  showOnline );

    if ( ! navigator.onLine ) {
        showOffline();
    }
}