jQuery( function ($) {
    "use strict";
    
    //#! DateTime pickers
    $( '.js-datepicker' ).datetimepicker( {
        format: 'd-m-Y H:i:s',
        step: 1
    } );
    
    var locale = ( typeof ( SqmLocale ) !== 'undefined' ? SqmLocale : false );
    if ( !locale ) {
        throw new Error( 'SqmLocale could not be found.' );
    }
    
    //<editor-fold desc=":: QUIZ QUESTIONS -- CREATE ::">
    var QuizQuestionCreateManager = {
        form: null,
        buttonAdd: null,
        formInnerWrap: null,
        idCounter: 0,
        __construct: function () {
            this.form = $( '#js-form-add-question' );
            this.buttonAdd = $( '.js-btn-add-question', this.form );
            this.formInnerWrap = $( '#js-form-inner-wrap', this.form );
        },
        __getFieldTemplate: function (id) {
            var html = '<div class="form-section js-section-question" id="section-__ID__" data-id="__ID__">';
            html += '<label for="title-__ID__">Question</label>';
            html += '<input id="title-__ID__" name="titles[]" type="text" class="widefat" value=""/>';
            html += '<button type="button" class="button button-link-delete button-small js-button-delete-question" data-parent="section-__ID__">Delete</button>';
            html += '<img src="' + locale.ajax.loader_uri + '" class="ajax-loader js-ajax-loader hidden js-ajax-loader" alt="">';
            html += '</div>';
            return html.replace( /__ID__/g, id );
        },
        /**
         * Hook up the event listener for deleting existent records
         * @param $context
         * @private
         */
        __hookButtonDeleteQuestions: function ($context) {
            $context.find( '.js-button-delete-question' ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if ( confirm( 'Are you sure you want to delete this question?' ) ) {
                    var button = $( this ),
                        ajaxLoader = $context.find( '.js-ajax-loader' );
                    button.addClass( 'no-click' );
                    ajaxLoader.removeClass( 'hidden' );
                    //
                    var ajaxConfig = {
                        url: locale.ajax.url,
                        method: 'post',
                        timeout: 25000,
                        cache: false,
                        async: true,
                        data: {
                            action: 'sqm_ajax',
                            request: 'delete_question',
                            [locale.ajax.nonce_name]: locale.ajax.nonce_value,
                            question_id: $context.data( 'id' ),
                        }
                    };
                    $.ajax( ajaxConfig )
                        .done( function (response) {
                            if ( response.success ) {
                                $context.remove();
                            }
                            else {
                                if ( response.data ) {
                                    alert( 'Error: ' + response.data );
                                }
                                else {
                                    alert( 'Error: No response from server' );
                                }
                            }
                        } )
                        .fail( function (x, s, e) {
                            ajaxLoader.addClass( 'hidden' );
                            button.removeClass( 'no-click' );
                            alert( 'ERROR: ' + e )
                        } );
                }
            } );
        },
        /**
         * Hook up the event listener for updating existent records
         * @param $context
         * @private
         */
        __hookButtonUpdateQuestion: function ($context) {
            var button = $( this ),
                ajaxLoader = $context.find( '.js-ajax-loader' ),
                questionID = $context.data( 'id' ),
                titleInput = $context.find( '#title-' + questionID );
            button.addClass( 'no-click' );
            ajaxLoader.removeClass( 'hidden' );
            //
            var ajaxConfig = {
                url: locale.ajax.url,
                method: 'post',
                timeout: 25000,
                cache: false,
                async: true,
                data: {
                    action: 'sqm_ajax',
                    request: 'update_question',
                    [locale.ajax.nonce_name]: locale.ajax.nonce_value,
                    quiz_id: $('#quiz_id').val(),
                    question_id: questionID,
                    title: titleInput.val(),
                }
            };
            $.ajax( ajaxConfig )
                .done( function (response) {
                    if ( response.success ) {
                        alert( response.data );
                    }
                    else {
                        if ( response.data ) {
                            alert( 'Error: ' + response.data );
                        }
                        else {
                            alert( 'Error: No response from server' );
                        }
                    }
                    ajaxLoader.addClass( 'hidden' );
                    button.removeClass( 'no-click' );
                } )
                .fail( function (x, s, e) {
                    ajaxLoader.addClass( 'hidden' );
                    button.removeClass( 'no-click' );
                    alert( 'ERROR: ' + e );
                } );
        },
        /**
         * Hook up the event listener for deleting the dynamically added records
         * @param $context
         * @private
         */
        __hookButtonDeleteTemplateQuestion: function ($context) {
            $context.find( '.js-button-delete-question' ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if ( confirm( 'Are you sure you want to delete this question?' ) ) {
                    $context.remove();
                }
            } );
        },
        
        init: function () {
            this.__construct();
            var $this = this;
            
            //#! Update counter based on the number of existent questions
            this.idCounter = $( '.js-section-question' ).length;
            
            this.buttonAdd.on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                
                $this.idCounter++;
                $this.formInnerWrap.append( $this.__getFieldTemplate( $this.idCounter ) );
                $this.__hookButtonDeleteTemplateQuestion( $( '.js-section-question[data-id="' + $this.idCounter + '"]' ) );
            } );
    
            $( '.js-button-update-question', this.form ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
        
                var self = $( this ),
                    $context = $( '#' + self.data( 'parent' ) )
        
                $this.__hookButtonUpdateQuestion( $context );
            } );
            
            //#! Hook delete existent questions
            this.formInnerWrap.find( '.js-section-question' ).each( function (i, el) {
                $this.__hookButtonDeleteQuestions( $( el ) );
            } );
        }
    };
    QuizQuestionCreateManager.init();
    //</editor-fold desc=":: QUIZ QUESTIONS -- CREATE ::">
    
    //<editor-fold desc=":: QUIZ ANSWERS -- CREATE ::">
    var QuizAnswerCreateManager = {
        form: null,
        buttonAdd: null,
        formInnerWrap: null,
        idCounter: 0,
        __construct: function () {
            this.form = $( '#js-form-add-answer' );
            this.buttonAdd = $( '.js-btn-add-answer', this.form );
            this.formInnerWrap = $( '#js-form-inner-wrap', this.form );
        },
        __getFieldTemplate: function (id) {
            var html = '<div class="form-section js-section-answer" id="section-answer-__ID__" data-id="__ID__">';
            html += '<p>';
            html += '<label for="title-__ID__">Answer</label>';
            html += '<input id="title-__ID__" name="answers[__ID__]" type="text" class="widefat" value=""/>';
            html += '</p>';
            html += '<p>';
            html += '<label for="points-__ID__">Points</label>';
            html += '<input id="points-__ID__" name="points[__ID__]" type="number" class="widefat" value="0" min="0" step="1"/>';
            html += '</p>';
            html += '<p>';
            html += '<label for="correct-__ID__">Is correct answer?</label>';
            html += '<input id="correct-__ID__" name="correct[__ID__]" type="checkbox" class="widefat" value="1"/>';
            html += '</p>';
            html += '<button type="button" class="button button-link-delete button-small js-button-delete-answer" data-parent="section-answer-__ID__">Delete</button>';
            html += '<img src="' + locale.ajax.loader_uri + '" class="ajax-loader js-ajax-loader hidden js-ajax-loader" alt="">';
            html += '</div>';
            return html.replace( /__ID__/g, id );
        },
        /**
         * Hook up the event listener for deleting existent records
         * @param $context
         * @private
         */
        __hookButtonDeleteAnswers: function ($context) {
            $context.find( '.js-button-delete-answer' ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if ( confirm( 'Are you sure you want to delete this answer?' ) ) {
                    var button = $( this ),
                        ajaxLoader = $context.find( '.js-ajax-loader' );
                    button.addClass( 'no-click' );
                    ajaxLoader.removeClass( 'hidden' );
                    //
                    var ajaxConfig = {
                        url: locale.ajax.url,
                        method: 'post',
                        timeout: 25000,
                        cache: false,
                        async: true,
                        data: {
                            action: 'sqm_ajax',
                            request: 'delete_answer',
                            [locale.ajax.nonce_name]: locale.ajax.nonce_value,
                            answer_id: $context.data( 'id' ),
                        }
                    };
                    $.ajax( ajaxConfig )
                        .done( function (response) {
                            if ( response.success ) {
                                $context.remove();
                            }
                            else {
                                if ( response.data ) {
                                    alert( 'Error: ' + response.data );
                                }
                                else {
                                    alert( 'Error: No response from server' );
                                }
                            }
                        } )
                        .fail( function (x, s, e) {
                            ajaxLoader.addClass( 'hidden' );
                            button.removeClass( 'no-click' );
                            alert( 'ERROR: ' + e );
                        } );
                }
            } );
        },
        /**
         * Hook up the event listener for updating existent records
         * @param $context
         * @private
         */
        __hookButtonUpdateAnswer: function ($context) {
            var button = $( this ),
                ajaxLoader = $context.find( '.js-ajax-loader' ),
                answerID = $context.data( 'id' ),
                titleInput = $context.find( '#title-' + answerID ),
                pointsInput = $context.find( '#points-' + answerID ),
                correctInput = $context.find( '#correct-' + answerID );
            button.addClass( 'no-click' );
            ajaxLoader.removeClass( 'hidden' );
            //
            var ajaxConfig = {
                url: locale.ajax.url,
                method: 'post',
                timeout: 25000,
                cache: false,
                async: true,
                data: {
                    action: 'sqm_ajax',
                    request: 'update_answer',
                    [locale.ajax.nonce_name]: locale.ajax.nonce_value,
                    question_id: $( '#question_id' ).val(),
                    answer_id: answerID,
                    title: titleInput.val(),
                    points: pointsInput.val(),
                    correct: correctInput.is( ':checked' ),
                }
            };
            $.ajax( ajaxConfig )
                .done( function (response) {
                    if ( response.success ) {
                        alert( response.data );
                    }
                    else {
                        if ( response.data ) {
                            alert( 'Error: ' + response.data );
                        }
                        else {
                            alert( 'Error: No response from server' );
                        }
                    }
                    ajaxLoader.addClass( 'hidden' );
                    button.removeClass( 'no-click' );
                } )
                .fail( function (x, s, e) {
                    ajaxLoader.addClass( 'hidden' );
                    button.removeClass( 'no-click' );
                    alert( 'ERROR: ' + e );
                } );
        },
        /**
         * Hook up the event listener for deleting the dynamically added records
         * @param $context
         * @private
         */
        __hookButtonDeleteTemplateAnswer: function ($context) {
            $context.find( '.js-button-delete-answer' ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if ( confirm( 'Are you sure you want to delete this answer?' ) ) {
                    $context.remove();
                }
            } );
        },
        
        init: function () {
            this.__construct();
            var $this = this;
            
            //#! Update counter based on the number of existent questions
            this.idCounter = $( '.js-section-answer' ).length;
            
            this.buttonAdd.on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                
                $this.idCounter++;
                $this.formInnerWrap.append( $this.__getFieldTemplate( $this.idCounter ) );
                $this.__hookButtonDeleteTemplateAnswer( $( '.js-section-answer[data-id="' + $this.idCounter + '"]' ) );
            } );
            
            $( '.js-button-update-answer', this.form ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                
                var self = $( this ),
                    $context = $( '#' + self.data( 'parent' ) )
                
                $this.__hookButtonUpdateAnswer( $context );
            } );
            
            //#! Hook delete existent answers
            this.formInnerWrap.find( '.js-section-answer' ).each( function (i, el) {
                $this.__hookButtonDeleteAnswers( $( el ) );
            } );
        }
    };
    QuizAnswerCreateManager.init();
    //</editor-fold desc=":: QUIZ ANSWERS -- CREATE ::">
    
} );
