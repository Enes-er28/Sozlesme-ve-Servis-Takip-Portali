  <footer class="main-footer">

  </footer>

<!-- jQuery -->
<!-- jQuery (tek bir kere, en başta) -->
<script src="plugins/jquery/jquery.min.js"></script>

<!-- jQuery UI (tek bir kere) -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- Bootstrap 4 Bundle (Popper içerir) -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap JS (modal, dropdown, tooltip vb. için) -->


<!-- Diğer eklentiler -->
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/sparklines/sparkline.js"></script>
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>


<script src="dist/js/pages/dashboard.js"></script>

<!-- DataTables (tek sefer) -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


<!-- diğer eklentiler -->



<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>

<!-- Page specific script -->
<!-- Moment.js ve datetime-moment -->
<script src="https://cdn.jsdelivr.net/npm/moment@2/min/moment.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>

<script>
$(function () {
  if ($('#example1').length) {

    // Önce DataTables'a tarih formatını tanıt
    $.fn.dataTable.moment('DD/MM/YYYY');

    // Türkçe sıralama için custom sort
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
      "turkish-pre": function (d) {
        return d.toLowerCase()
                .replace(/ç/g, 'c')
                .replace(/ğ/g, 'g')
                .replace(/ı/g, 'i')
                .replace(/ö/g, 'o')
                .replace(/ş/g, 's')
                .replace(/ü/g, 'u');
      }
    });

    var colCount = $('#example1 thead tr th').length;
    var orderIndex = colCount > 9 ? 9 : 1;

    var table = $("#example1").DataTable({
      responsive: true,
      lengthChange: true,
      autoWidth: false,
      order: [[orderIndex, "asc"]],
      columnDefs: [
        { type: 'turkish', targets: orderIndex }
        // Eğer tarih sütununun indexini biliyorsan buraya type: 'date' ekleyebilirsin:
        // { type: 'date', targets: 2 }
      ],
      buttons: ["csv", "excel", "pdf"],
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tümü"]],
      pageLength: 25,
      language: {
          "lengthMenu": "Sayfada _MENU_ kayıt göster",
          "zeroRecords": "Kayıt bulunamadı",
          "info": "_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
          "infoEmpty": "Gösterilecek kayıt yok",
          "infoFiltered": "(_MAX_ toplam kayıttan filtrelendi)",
          "search": "Ara:",
          "paginate": {
              "first": "İlk",
              "last": "Son",
              "next": "Sonraki",
              "previous": "Önceki"
          }
      },
      dom: '<"d-flex justify-content-between mb-2"<"d-flex align-items-center"l><"d-flex ml-auto"f>>rtip'
    });

    // Alt alanı oluştur
    $('#example1_wrapper').append(`
      <div class="d-flex justify-content-between mt-2 bottom-controls">
        <div class="button-container"></div>
          <div class="info-text" style="flex: 1; text-align: center;"></div>
          <div class="pagination-controls"></div>
      </div>
    `);

    // Butonları sol alt container’a taşı
    table.buttons().container().appendTo('#example1_wrapper .button-container');

    // Info metnini ortada olacak şekilde sağ alt container’a taşı
    $('#example1_wrapper .dataTables_info').appendTo('#example1_wrapper .info-text');

    // Sayfalama kontrollerini sağ alt container’a taşı
    $('#example1_wrapper .dataTables_paginate').appendTo('#example1_wrapper .pagination-controls');
  }
});
</script>




</body>
</html>
