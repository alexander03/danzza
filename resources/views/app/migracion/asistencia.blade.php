<h2>Reporte de Asistencia</h2>
<div style="display: block;
    box-sizing: border-box;
    height: 800px;
    width: 800px;">
  <canvas id="myChart"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/emn178/chartjs-plugin-labels/src/chartjs-plugin-labels.js"></script>
<script>
  const ctx = document.getElementById('myChart');

  new Chart(ctx, {
    type: 'polarArea',
    data: {
      labels: ['Asistencias', 'Inasistencias'],
      datasets: [{
        label: '# de Asistencia',
        data: {{ $data }},
        backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        labels: [
          {
            render: 'value',
            fontColor: ['green', 'red'],
            precision: 2
          }
        ]
      }
    }
  });
</script>