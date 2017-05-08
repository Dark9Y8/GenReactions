/* global document */
/* global jQuery */
/* global gen */

// global namespace
if ( typeof window.gen === 'undefined' ) {
    window.gen = {};
}

/********
 *
 * Core
 *
 *******/

(function ($, ctx) {

    'use strict';

    /** VARS *************************/

    ctx.config = $.parseJSON(window.gen_front_config);

    if (!ctx.config) {
        throw 'gen Error: Global config is not defined!';
    }

    /** FUNCTIONS ********************/

    ctx.isTouchDevice = function () {
        return ('ontouchstart' in window) || navigator.msMaxTouchPoints;
    };

    ctx.createCookie =  function (name, value, days) {
        var expires;

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        else {
            expires = '';
        }

        document.cookie = name.concat('=', value, expires, '; path=/');
    };

    ctx.readCookie = function (name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');

        for(var i = 0; i < ca.length; i += 1) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1,c.length);
            }

            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length,c.length);
            }
        }

        return null;
    };

    ctx.deleteCookie = function (name) {
        ctx.createCookie(name, '', -1);
    };

})(jQuery, gen);

/*********************
 *
 * Module: Reactions
 *
 ********************/

(function ($, ctx) {

    'use strict';

    var selectors = {
        'wrapper':      '.gen-reaction-items',
        'link':         '.gen-reaction',
        'voted':        '.gen-reaction-voted',
        'value':        '.gen-reaction-value',
        'bar':          '.gen-reaction-bar'
    };

    var classes = {
        'voted':        'gen-reaction-voted'
    };

    ctx.reactionsSelectors  = selectors;
    ctx.reactionsClasses    = classes;

    ctx.reactions = function () {
        var $body = $('body');

        // Catch event on wrapper to keep it working after box content reloading
        $body.on('click', selectors.link, function (e) {
            e.preventDefault();

            var $link       = $(this);
            var $wrapper    = $link.parents(selectors.wrapper);
            var nonce       = $.trim($link.attr('data-gen-nonce'));
            var postId      = parseInt($link.attr('data-gen-post-id'), 10);
            var authorId    = parseInt($link.attr('data-gen-author-id'), 10);
            var type        = $.trim($link.attr('data-gen-reaction'));

            if ($link.is(selectors.voted)) {
                return;
            }

            ctx.reactionVote({
                'postId':   postId,
                'authorId': authorId,
                'type':     type
            }, nonce, $wrapper);
        });

        // Update reactions for guests.
        if (!$body.is('.logged-in')) {
            $(selectors.link).each(function () {
                var $link  = $(this);
                var postId = parseInt($link.attr('data-gen-post-id'), 10);
                var type   = $.trim($link.attr('data-gen-reaction'));
                var reactionVoted = ctx.readCookie('gen_vote_'+ type +'_' + postId);

                if (reactionVoted) {
                    $link.addClass(classes.voted);
                } else {
                    $link.removeClass(classes.voted);
                }
            });
        }
    };

    ctx.reactionVote = function (data, nonce, $box) {
        var config = $.parseJSON(window.gen_front_config);

        if (!config) {
            ctx.log('Post voting failed. Global config is not defined!');
            return;
        }

        var xhr = $.ajax({
            'type': 'POST',
            'url': config.ajax_url,
            'dataType': 'json',
            'data': {
                'action':           'gen_vote_post',
                'security':         nonce,
                'gen_post_id':      data.postId,
                'gen_author_id':    data.authorId,
                'gen_vote_type':    data.type
            }
        });

        // Update state, without waiting for ajax response.
        var $reactions = $box.find(selectors.link);
        var reactions = ctx.getNewState($reactions, data.type);

        ctx.reactionVoted(data.postId, data.type, $box);

        // Update states.
        $reactions.each(function () {
            var $this = $(this);

            var type = $.trim($this.attr('data-gen-reaction'));

            if (typeof reactions[type] !== 'undefined') {
                $this.find(selectors.value).text(reactions[type].count);
                $this.find(selectors.bar).css('height', reactions[type].percentage + '%');
            }
        });

        xhr.done(function (res) {
            if (res.status !== 'success') {
                alert('Some error occurred while voting. Please try again.');
            }
        });
    };

    ctx.getNewState = function($reactions, votedType) {
        var state = {};
        var total = 0;

        $reactions.each(function () {
            var $this = $(this);
            var type = $.trim($this.attr('data-gen-reaction'));
            var count = parseInt($this.find(selectors.value).text(), 10);

            state[type] = {
                'count':        count,
                'percentage':   ''
            };

            total += count;
        });

        if (typeof state[votedType] !== 'undefined') {
            state[votedType]['count']++;
            total++;
        }

        // Recalculate percentages.
        for (var type in state) {
            state[type]['percentage'] = Math.round( ( 100 * state[type]['count'] ) / total );
        }

        return state;
    };

    ctx.reactionVoted = function(postId, type, $box) {
        var cookieName   = 'gen_vote_'+ type +'_' + postId;

        ctx.createCookie(cookieName, true, 30);

        // Cookie can't be read immediately so we need to update CSS classes manually.
        $box.find('.gen-reaction-' + type).addClass(classes.voted);
    };

    // fire
    $(document).ready(function () {
        ctx.reactions();
    });

})(jQuery, gen);
