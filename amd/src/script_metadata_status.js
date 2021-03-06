define(['jquery', 'core/ajax', 'core/notification'], ($, ajax, notification) => {
    return {
        init: (params) => {
            $(() => {
                const courseId = params.courseId;
                const context = params.context;

                const promises = ajax.call([
                    {
                        methodname: 'block_metadata_status_get_modules_status',
                        args: {
                            courseId: courseId,
                            context: context
                        },
                        fail: notification.exception
                    }
                ]);

                $.when(promises[0]).done((moduleStatus) => {
                    injectHTML(moduleStatus);

                    $('.block_metadata_status_progress').css({
                        'background-color': moduleStatus.options.progressBarBackgroundColor,
                        'border-color': moduleStatus.options.progressBarBackgroundColor
                    });
                    $('.block_metadata_status_shared_icon.disabled').css({
                        'fill': moduleStatus.options.progressBarColorBeforeThreshold
                    });
                    $('.block_metadata_status_shared_icon.enabled').css({
                        'fill': moduleStatus.options.progressBarColorAfterThreshold
                    });
                });

                /**
                 * Inject Metadata Status HTML
                 *
                 * @param {
                 * {
                 *  modules: {
                 *      id: number,
                 *      status: {
                 *          percentage: number,
                 *          shared: boolean
                 *      }
                 *  }[],
                 *  options: {
                 *      enablePercentageLabel: string,
                 *      progressBarBackgroundColor: string,
                 *      progressBarThreshold: number,
                 *      progressBarColorBeforeThreshold: string,
                 *      progressBarColorAfterThreshold: string
                 *      }
                 *  }
                 * } moduleStatus
                 */
                function injectHTML(moduleStatus) {
                    moduleStatus.modules.forEach((module) => {
                        $('#module-' + module.id + ' .actions')
                            .prepend(
                                getHTML(module.status.percentage, module.status.shared, moduleStatus.options.enablePercentageLabel)
                            );
                        $('#module-' + module.id + ' .block_metadata_status_progress_bar').css({
                            'background-color': (module.status.percentage < (moduleStatus.options.progressBarThreshold * 10)) ?
                                moduleStatus.options.progressBarColorBeforeThreshold :
                                moduleStatus.options.progressBarColorAfterThreshold
                        });
                    });
                }

                /**
                 *
                 * @param {number} percentage
                 * @param {boolean} shared
                 * @param {boolean} enablePercentageLabel
                 *
                 * @returns {string}
                 */
                function getHTML(percentage, shared, enablePercentageLabel) {
                    /* eslint-disable max-len */
                    return `<div class="block_metadata_status_container position-absolute d-inline-block">
                                <svg class="block_metadata_status_shared_icon align-middle ` + (shared ? `enabled` : `disabled`) + `" height="14px" width="14px" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M 12.96875 2.332031 C 12.96875 3.378906 12.121094 4.230469 11.074219 4.230469 C 10.027344 4.230469 9.179688 3.378906 9.179688 2.332031 C 9.179688 1.285156 10.027344 0.4375 11.074219 0.4375 C 12.121094 0.4375 12.96875 1.285156 12.96875 2.332031 Z M 12.96875 2.332031 " />
                                    <path d="M 11.074219 4.667969 C 9.789062 4.667969 8.742188 3.621094 8.742188 2.332031 C 8.742188 1.046875 9.789062 0 11.074219 0 C 12.359375 0 13.40625 1.046875 13.40625 2.332031 C 13.40625 3.621094 12.359375 4.667969 11.074219 4.667969 Z M 11.074219 0.875 C 10.269531 0.875 9.617188 1.53125 9.617188 2.332031 C 9.617188 3.136719 10.269531 3.792969 11.074219 3.792969 C 11.878906 3.792969 12.53125 3.136719 12.53125 2.332031 C 12.53125 1.53125 11.878906 0.875 11.074219 0.875 Z M 11.074219 0.875 " />
                                    <path d="M 12.96875 11.667969 C 12.96875 12.714844 12.121094 13.5625 11.074219 13.5625 C 10.027344 13.5625 9.179688 12.714844 9.179688 11.667969 C 9.179688 10.621094 10.027344 9.769531 11.074219 9.769531 C 12.121094 9.769531 12.96875 10.621094 12.96875 11.667969 Z M 12.96875 11.667969 " />
                                    <path d="M 11.074219 14 C 9.789062 14 8.742188 12.953125 8.742188 11.667969 C 8.742188 10.378906 9.789062 9.332031 11.074219 9.332031 C 12.359375 9.332031 13.40625 10.378906 13.40625 11.667969 C 13.40625 12.953125 12.359375 14 11.074219 14 Z M 11.074219 10.207031 C 10.269531 10.207031 9.617188 10.863281 9.617188 11.667969 C 9.617188 12.46875 10.269531 13.125 11.074219 13.125 C 11.878906 13.125 12.53125 12.46875 12.53125 11.667969 C 12.53125 10.863281 11.878906 10.207031 11.074219 10.207031 Z M 11.074219 10.207031 " />
                                    <path d="M 4.804688 7 C 4.804688 8.046875 3.953125 8.894531 2.90625 8.894531 C 1.859375 8.894531 1.011719 8.046875 1.011719 7 C 1.011719 5.953125 1.859375 5.105469 2.90625 5.105469 C 3.953125 5.105469 4.804688 5.953125 4.804688 7 Z M 4.804688 7 " />
                                    <path d="M 2.90625 9.332031 C 1.621094 9.332031 0.574219 8.285156 0.574219 7 C 0.574219 5.714844 1.621094 4.667969 2.90625 4.667969 C 4.195312 4.667969 5.242188 5.714844 5.242188 7 C 5.242188 8.285156 4.195312 9.332031 2.90625 9.332031 Z M 2.90625 5.542969 C 2.101562 5.542969 1.449219 6.195312 1.449219 7 C 1.449219 7.804688 2.101562 8.457031 2.90625 8.457031 C 3.710938 8.457031 4.367188 7.804688 4.367188 7 C 4.367188 6.195312 3.710938 5.542969 2.90625 5.542969 Z M 2.90625 5.542969 " />
                                    <path d="M 4.285156 6.71875 C 4.082031 6.71875 3.882812 6.613281 3.777344 6.425781 C 3.617188 6.144531 3.714844 5.789062 3.996094 5.628906 L 9.40625 2.542969 C 9.6875 2.382812 10.042969 2.480469 10.203125 2.761719 C 10.363281 3.042969 10.265625 3.398438 9.984375 3.558594 L 4.574219 6.644531 C 4.480469 6.695312 4.382812 6.71875 4.285156 6.71875 Z M 4.285156 6.71875 " />
                                    <path d="M 9.695312 11.53125 C 9.597656 11.53125 9.5 11.507812 9.410156 11.457031 L 3.996094 8.371094 C 3.714844 8.210938 3.617188 7.855469 3.777344 7.574219 C 3.9375 7.292969 4.292969 7.195312 4.574219 7.355469 L 9.988281 10.441406 C 10.265625 10.601562 10.363281 10.957031 10.203125 11.238281 C 10.097656 11.425781 9.898438 11.53125 9.695312 11.53125 Z M 9.695312 11.53125 " />
                                </svg>
                                <div class="block_metadata_status_progress d-inline-block align-middle">
                                  <div class="block_metadata_status_progress_bar h-100 float-left" style="width:` + percentage + `%;"></div>
                                </div>` +
                                (enablePercentageLabel ? `<div class="block_metadata_status_pourcentage d-inline-block align-middle ml-1">` + percentage + `%</div>` : ``) +
                        `</div>`;
                    /* eslint-enable max-len */
                }
            });
        }
    };
});