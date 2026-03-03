  $(document).ready(function() {
      $(function () {
        function formatPhoneForDisplay(rawPhone) {
          var digits = String(rawPhone || '').replace(/\D+/g, '');

          if (!digits) {
            return '';
          }

          if (digits.length === 11) {
            return digits.slice(0, 4) + '-' + digits.slice(4, 7) + '-' + digits.slice(7);
          }

          return digits.replace(/(\d{3})(?=\d)/g, '$1 ').trim();
        }

        $('.js-phone-tooltip').each(function () {
          var phone = $(this).data('phone');
          var formattedPhone = formatPhoneForDisplay(phone);

          if (formattedPhone) {
            $(this)
              .attr('data-original-title', formattedPhone)
              .attr('title', formattedPhone)
              .attr('dir', 'ltr');
          }
        });

        $('[data-toggle="tooltip"]').tooltip();
      })
      $().ready(function() {
        $sidebar = $('.sidebar');
        $navbar = $('.navbar');
        $main_panel = $('.main-panel');

        $full_page = $('.full-page');

        $sidebar_responsive = $('body > .navbar-collapse');
        sidebar_mini_active = true;
        white_color = false;

        window_width = $(window).width();

        fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();

        var storageKeys = {
          color: 'dashboard.sidebarColor',
          theme: 'dashboard.theme',
          sidebarMini: 'dashboard.sidebarMini'
        };

        function saveSetting(key, value) {
          try {
            localStorage.setItem(key, value);
          } catch (e) {}
        }

        function loadSetting(key) {
          try {
            return localStorage.getItem(key);
          } catch (e) {
            return null;
          }
        }

        function applyPersistedSettings() {
          var savedColor = loadSetting(storageKeys.color);
          var savedTheme = loadSetting(storageKeys.theme);
          var savedSidebarMini = loadSetting(storageKeys.sidebarMini);

          if (savedColor) {
            if ($sidebar.length !== 0) {
              $sidebar.attr('data', savedColor);
            }
            if ($main_panel.length !== 0) {
              $main_panel.attr('data', savedColor);
            }
            if ($full_page.length !== 0) {
              $full_page.attr('filter-color', savedColor);
            }
            if ($sidebar_responsive.length !== 0) {
              $sidebar_responsive.attr('data', savedColor);
            }

            $('.fixed-plugin .background-color span').removeClass('active');
            $('.fixed-plugin .background-color span[data-color="' + savedColor + '"]').addClass('active');
          }

          if (savedTheme === 'light') {
            $('body').addClass('white-content');
            white_color = true;
          } else {
            $('body').removeClass('white-content');
            white_color = false;
          }

          if (savedSidebarMini === '1') {
            $('body').addClass('sidebar-mini');
            sidebar_mini_active = true;
          } else if (savedSidebarMini === '0') {
            $('body').removeClass('sidebar-mini');
            sidebar_mini_active = false;
          }
        }

        applyPersistedSettings();



        $('.fixed-plugin a').click(function(event) {
          if ($(this).hasClass('switch-trigger')) {
            if (event.stopPropagation) {
              event.stopPropagation();
            } else if (window.event) {
              window.event.cancelBubble = true;
            }
          }
        });

        $('.fixed-plugin .background-color span').click(function() {
          $(this).siblings().removeClass('active');
          $(this).addClass('active');

          var new_color = $(this).data('color');

          if ($sidebar.length != 0) {
            $sidebar.attr('data', new_color);
          }

          if ($main_panel.length != 0) {
            $main_panel.attr('data', new_color);
          }

          if ($full_page.length != 0) {
            $full_page.attr('filter-color', new_color);
          }

          if ($sidebar_responsive.length != 0) {
            $sidebar_responsive.attr('data', new_color);
          }

          saveSetting(storageKeys.color, new_color);
        });

        $('.switch-sidebar-mini input').on("switchChange.bootstrapSwitch", function() {
          var $btn = $(this);

          if (sidebar_mini_active == true) {
            $('body').removeClass('sidebar-mini');
            sidebar_mini_active = false;
            saveSetting(storageKeys.sidebarMini, '0');
            blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
          } else {
            $('body').addClass('sidebar-mini');
            sidebar_mini_active = true;
            saveSetting(storageKeys.sidebarMini, '1');
            blackDashboard.showSidebarMessage('Sidebar mini activated...');
          }

          // we simulate the window Resize so the charts will get updated in realtime.
          var simulateWindowResize = setInterval(function() {
            window.dispatchEvent(new Event('resize'));
          }, 180);

          // we stop the simulation of Window Resize after the animations are completed
          setTimeout(function() {
            clearInterval(simulateWindowResize);
          }, 1000);
        });

        $('.switch-change-color input').on("switchChange.bootstrapSwitch", function() {
          var $btn = $(this);

          if (white_color == true) {

            $('body').addClass('change-background');
            setTimeout(function() {
              $('body').removeClass('change-background');
              $('body').removeClass('white-content');
            }, 900);
            white_color = false;
            saveSetting(storageKeys.theme, 'dark');
          } else {

            $('body').addClass('change-background');
            setTimeout(function() {
              $('body').removeClass('change-background');
              $('body').addClass('white-content');
            }, 900);

            white_color = true;
            saveSetting(storageKeys.theme, 'light');
          }


        });

        $('.light-badge').click(function() {
          $('body').addClass('white-content');
          white_color = true;
          saveSetting(storageKeys.theme, 'light');
        });

        $('.dark-badge').click(function() {
          $('body').removeClass('white-content');
          white_color = false;
          saveSetting(storageKeys.theme, 'dark');
        });
      });

          $(function () {
      $('input[type="date"]').each(function () {
        var $input = $(this);
        var currentValue = $input.val();

        if ($input.data('datepicker')) {
          $input.datepicker('destroy');
        }

        $input.attr('type', 'text');
        $input.attr('autocomplete', 'off');
        $input.attr('dir', 'rtl');
        $input.attr('lang', 'ar');

        $input.datepicker({
          format: 'yyyy-mm-dd',
          language: 'ar',
          rtl: true,
          autoclose: true,
          todayHighlight: true,
          clearBtn: true,
          orientation: 'bottom auto',
          container: 'body',
          zIndexOffset: 2000,
          beforeShowDay: function () {
            return true;
          }
        });

        if (currentValue) {
          $input.datepicker('update', currentValue);
        }
      });
    });
    });
    