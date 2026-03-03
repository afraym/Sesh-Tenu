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
        });

        $('.switch-sidebar-mini input').on("switchChange.bootstrapSwitch", function() {
          var $btn = $(this);

          if (sidebar_mini_active == true) {
            $('body').removeClass('sidebar-mini');
            sidebar_mini_active = false;
            blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
          } else {
            $('body').addClass('sidebar-mini');
            sidebar_mini_active = true;
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
          } else {

            $('body').addClass('change-background');
            setTimeout(function() {
              $('body').removeClass('change-background');
              $('body').addClass('white-content');
            }, 900);

            white_color = true;
          }


        });

        $('.light-badge').click(function() {
          $('body').addClass('white-content');
        });

        $('.dark-badge').click(function() {
          $('body').removeClass('white-content');
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
    