
jQuery(document).ready(function($){

    function wsTeamSkeletonCard(){
        return [
            '<div class="ws-team-skeleton-card" aria-hidden="true">',
                '<div class="ws-team-skeleton-header">',
                    '<span class="ws-team-skeleton-line ws-team-skeleton-line-wide"></span>',
                    '<span class="ws-team-skeleton-line ws-team-skeleton-line-medium"></span>',
                '</div>',
                '<div class="ws-team-skeleton-media"></div>',
            '</div>'
        ].join('');
    }

    function wsTeamSkeletonCards(count){
        let skeletons = '';

        for (let i = 0; i < count; i++) {
            skeletons += wsTeamSkeletonCard();
        }

        return skeletons;
    }

    function wsTeamTableHeader(){
        return [
            '<div class="ws-team-table-header" aria-hidden="true">',
                '<span>Photo</span>',
                '<span>Team Member</span>',
                '<span>Designation</span>',
                '<span>Experience</span>',
                '<span>Action</span>',
            '</div>'
        ].join('');
    }

    function wsTeamSyncTableHeader(wrapper){
        let grid = wrapper.find('.ws-team-grid');

        grid.find('.ws-team-table-header').remove();

        if (wrapper.hasClass('ws-team-table-view') && grid.find('.ws-team-card').length) {
            grid.prepend(wsTeamTableHeader());
        }
    }

    function wsTeamSyncLoadMore(wrapper, html){
        wrapper.find('.ws-team-load-more-wrap').remove();

        if (html) {
            wrapper.find('.ws-team-grid').after(html);
        }
    }

    $(document).on('change', '.ws-team-category-filter', function(){

        let select = $(this);
        let wrapper = select.closest('.ws-team-wrapper');
        let grid = wrapper.find('.ws-team-grid');
        let filterWrap = select.closest('.ws-team-filter-wrap');
        let loadStartedAt = Date.now();
        let loadId = loadStartedAt + '-' + Math.random();
        let skeletonCount = Math.max(grid.find('.ws-team-card').length, 3);
        let nextGridHtml = '<div class="ws-team-empty">No team members found.</div>';
        let nextLoadMoreHtml = '';

        wrapper.data('wsTeamLoadId', loadId);
        wrapper.data('term-id', select.val());
        grid.addClass('ws-team-grid-loading');
        filterWrap.addClass('ws-team-filter-loading');
        wrapper.find('.ws-team-load-more-wrap').remove();
        grid.html(wsTeamSkeletonCards(skeletonCount));

        $.ajax({
            url: wsTeam.ajaxurl,
            type:'POST',
            dataType:'json',
            data:{
                action:'ws_team_filter',
                nonce:wsTeam.filterNonce,
                term_id:select.val(),
                post_type:wrapper.data('post-type'),
                taxonomy:wrapper.data('taxonomy'),
                orderby:wrapper.data('orderby'),
                order:wrapper.data('order'),
                posts_per_page:wrapper.data('posts-per-page')
            },
            success:function(response){
                if (response && response.success && response.data) {
                    nextGridHtml = response.data.has_posts ? response.data.html : nextGridHtml;
                    nextLoadMoreHtml = response.data.load_more || '';
                }
            },
            complete:function(){
                let elapsed = Date.now() - loadStartedAt;
                let delay = Math.max(3000 - elapsed, 0);

                setTimeout(function(){
                    if (wrapper.data('wsTeamLoadId') !== loadId) {
                        return;
                    }

                    grid.html(nextGridHtml);
                    grid.removeClass('ws-team-grid-loading');
                    filterWrap.removeClass('ws-team-filter-loading');
                    wsTeamSyncLoadMore(wrapper, nextLoadMoreHtml);
                    wsTeamSyncTableHeader(wrapper);
                }, delay);
            }
        });

    });

    $(document).on('change', '.ws-dark-toggle-input', function(){

        $(this)
            .closest('.ws-team-wrapper')
            .toggleClass('ws-dark-enabled', this.checked);

    });

    $(document).on('click', '.ws-layout-toggle-button', function(){

        let button = $(this);
        let wrapper = button.closest('.ws-team-wrapper');
        let layout = button.data('layout');
        let toggle = button.closest('.ws-layout-toggle');
        let buttons = button.closest('.ws-layout-toggle').find('.ws-layout-toggle-button');

        wrapper.toggleClass('ws-team-table-view', layout === 'table');
        toggle.toggleClass('ws-layout-toggle-table', layout === 'table');
        wsTeamSyncTableHeader(wrapper);
        buttons
            .removeClass('ws-layout-toggle-button-active')
            .attr('aria-pressed', 'false');
        button
            .addClass('ws-layout-toggle-button-active')
            .attr('aria-pressed', 'true');

    });

    $(document).on('click', '.ws-team-load-more', function(){

        let button = $(this);
        let wrapper = button.closest('.ws-team-wrapper');
        let grid = wrapper.find('.ws-team-grid');
        let originalText = button.text();
        let restoreButton = function(){
            button
                .prop('disabled', false)
                .removeClass('ws-team-load-more-loading')
                .text(originalText);
        };

        if (button.prop('disabled')) {
            return;
        }

        button
            .prop('disabled', true)
            .addClass('ws-team-load-more-loading')
            .text('Loading...');

        $.ajax({
            url: wsTeam.ajaxurl,
            type:'POST',
            dataType:'json',
            data:{
                action:'ws_team_load_more',
                nonce:wsTeam.filterNonce,
                page:button.data('page'),
                term_id:wrapper.data('term-id') || 0,
                post_type:wrapper.data('post-type'),
                taxonomy:wrapper.data('taxonomy'),
                orderby:wrapper.data('orderby'),
                order:wrapper.data('order'),
                posts_per_page:wrapper.data('posts-per-page')
            },
            success:function(response){
                if (!response || !response.success || !response.data) {
                    restoreButton();
                    return;
                }

                if (response.data.html) {
                    grid.append(response.data.html);
                    wsTeamSyncTableHeader(wrapper);
                }

                if (response.data.has_more) {
                    button
                        .data('page', response.data.page)
                        .attr('data-page', response.data.page);
                    restoreButton();
                } else {
                    button.closest('.ws-team-load-more-wrap').remove();
                }
            },
            error:function(){
                restoreButton();
            }
        });

    });

    $(document).on('click', '.ws-popup-open', function(){

        let postID = $(this).data('id');
        let wrapper = $(this).closest('.ws-team-wrapper');
        let popup = wrapper.find('.ws-popup-container');
        let overlay = wrapper.find('.ws-popup-overlay');
        let effect = wrapper.data('popup-effect') || 'fade';

        $.ajax({
            url: wsTeam.ajaxurl,
            type:'POST',
            data:{
                action:'ws_team_popup',
                post_id:postID
            },
            success:function(response){

                popup
                    .removeClass('ws-popup-effect-fade ws-popup-effect-zoom ws-popup-effect-slide-up ws-popup-visible ws-popup-closing')
                    .addClass('ws-popup-effect-' + effect)
                    .html(response);
                overlay.addClass('ws-popup-overlay-visible');

                requestAnimationFrame(function(){
                    popup.addClass('ws-popup-visible');
                });

            }
        });

    });

    $(document).on('click', '.ws-popup-close, .ws-popup-overlay', function(){

        let wrapper = $(this).closest('.ws-team-wrapper');
        let popup = wrapper.find('.ws-popup-container');
        let overlay = wrapper.find('.ws-popup-overlay');
        let effect = wrapper.data('popup-effect') || 'fade';

        popup.addClass('ws-popup-closing');
        overlay.removeClass('ws-popup-overlay-visible');

        setTimeout(function(){
            popup
                .removeClass('ws-popup-visible ws-popup-closing ws-popup-effect-fade ws-popup-effect-zoom ws-popup-effect-slide-up')
                .empty();
        }, 300);

    });

});
