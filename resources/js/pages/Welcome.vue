<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, computed } from "vue";
import counterUp from "counterup2";

const getRandomStart = () => Math.floor(Math.random() * (600 - 500 + 1)) + 500;

const investorCount = ref(getRandomStart());

onMounted(() => {
    setInterval(() => {
        const increment = Math.floor(Math.random() * 5) + 1;
        investorCount.value += increment;
    }, 450000); 
});

defineProps<{
  canLogin?: boolean;
  canRegister?: boolean;
  laravelVersion: string;
  phpVersion: string;
}>();

// --- LÓGICA DEL SIMULADOR ---

// Datos de los planes (reflejan tu tabla 'plans')
const plans = ref([
  {
    id: 1,
    name: 'Plan Básico',
    calculation_type: 'fixed_plus_final',
    fixed_percentage: 15,
    closed_profit_percentage: 50,
  },
  {
    id: 2,
    name: 'Plan Premium',
    calculation_type: 'equal_installments',
    fixed_percentage: 15,
    closed_profit_percentage: 50,
  },
]);

// Formulario reactivo para el simulador
const form = ref({
  plan_id: '',
  amount: '',
  investment_contract_type: 'abierta',
});

// Propiedad computada que hace los cálculos en tiempo real
const calculatedPayments = computed(() => {
  if (!form.value.amount || !form.value.plan_id) return [];
  const amount = parseFloat(form.value.amount);
  if (isNaN(amount) || amount <= 0) return [];
  const selectedPlan = plans.value.find(p => p.id === parseInt(form.value.plan_id));
  if (!selectedPlan) return [];

  const payments = [];

  if (form.value.investment_contract_type === 'cerrada') {
    const percentage = selectedPlan.closed_profit_percentage ?? 50;
    const baseProfit = amount * (percentage / 100);
    const totalProfit = baseProfit * 3;
    const totalPayment = amount + totalProfit;
    payments.push({ label: `Pago Único a 90 días`, value: totalPayment });

  } else { // 'abierta'
    if (selectedPlan.calculation_type === 'fixed_plus_final' && selectedPlan.fixed_percentage) {
      const fixedPayment = amount * (selectedPlan.fixed_percentage / 100);
      for (let i = 1; i <= 5; i++) {
        payments.push({ label: `Pago ${i}`, value: fixedPayment });
      }
      const finalPayment = amount + fixedPayment;
      payments.push({ label: `Pago Final`, value: finalPayment });
    }
    else if (selectedPlan.calculation_type === 'equal_installments' && selectedPlan.fixed_percentage) {
      const fixedPayment = amount * (selectedPlan.fixed_percentage / 100);
      const totalProfit = fixedPayment * 6;
      const totalToPay = amount + totalProfit;
      const installment = totalToPay / 6;
      for (let i = 1; i <= 6; i++) {
        payments.push({ label: `Pago ${i} de 6`, value: installment });
      }
    }
  }

  return payments.map(p => ({
    ...p,
    formattedValue: new Intl.NumberFormat('es-CO', {
      style: 'currency', currency: 'COP', minimumFractionDigits: 0
    }).format(p.value)
  }));
});

// --- LÓGICA DEL COUNTERUP (TU CÓDIGO ORIGINAL) ---
onMounted(() => {
  const counters = document.querySelectorAll<HTMLElement>('[data-toggle="counter-up"]');
  counters.forEach((el) => {
    counterUp(el, {
      duration: 2000,
      delay: 16
    });
  });
});
</script>

<template>

  <Head>
    <meta charset="utf-8">
    <title>EON - Grupo Empresarial</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

  </Head>

  <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner"></div>
</div> -->
  <!-- Spinner End -->


  <!-- Topbar Start -->
  <!-- <div class="container-fluid bg-dark px-5 d-none d-lg-block">
        <div class="row gx-0">
            <div class="col-lg-8 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <small class="me-3 text-light"><i class="fa fa-map-marker-alt me-2"></i>123 Street, New York, USA</small>
                    <small class="me-3 text-light"><i class="fa fa-phone-alt me-2"></i>+012 345 6789</small>
                    <small class="text-light"><i class="fa fa-envelope-open me-2"></i>info@example.com</small>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-instagram fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href=""><i class="fab fa-youtube fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div> -->
  <!-- Topbar End -->


  <!-- Navbar & Carousel Start -->
  <div class="container-fluid position-relative p-0">
    <nav class="navbar navbar-expand-lg navbar-dark px-5 py-3 py-lg-0">
      <a href="#" class="navbar-brand p-0 d-flex align-items-center navbar-brand-responsive">

        <div class="logo-container">
          <img src="img/icons/icon-72x72.png" alt="Logo EON">
        </div>

        <div class="logo-text">
          <h1 class="m-0">
            EON
            <small>Grupo Empresarial</small>
          </h1>
        </div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0">
          <a href="#" class="nav-item nav-link active">Inicio</a>
          <a href="#" class="nav-item nav-link">Nosotros</a>
          <a href="#" class="nav-item nav-link">Proyectos</a>
        </div>
        <Link :href="route('login')" class="btn btn-primary py-2 px-4 ms-3">
        Iniciar Sesión
        </Link>
      </div>
    </nav>

    <div id="header-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel"
      style="background-color: black;">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img class="w-100" src="img/carousel-1.jpg" alt="Image">
          <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
            <div class="p-3" style="max-width: 900px;">
              <!-- <h5 class="text-white text-uppercase mb-3 animated slideInDown">Creative & Innovative</h5> -->
              <h1 class="display-1 text-white mb-md-4 animated zoomIn">Cumple tus metas invirtiendo seguro</h1>
              <Link :href="route('login')" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft d-lg-none">
              Iniciar Sesión
              </Link>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <img class="w-100" src="img/carousel-2.jpg" alt="Image">
          <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
            <div class="p-3" style="max-width: 900px;">
              <!-- <h5 class="text-white text-uppercase mb-3 animated slideInDown">Creative & Innovative</h5> -->
              <h1 class="display-1 text-white mb-md-4 animated zoomIn">Cumple tus metas invirtiendo seguro</h1>
              <Link :href="route('login')" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft d-lg-none">
              Iniciar Sesión
              </Link>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
      </button>
    </div>
  </div>
  <!-- Navbar & Carousel End -->

  <!-- Facts Start -->
  <div id="facts-section" class="container-fluid facts py-5 pt-lg-0">
    <div class="container py-5 pt-lg-0">
      <div class="row gx-0">
        <div class="col-lg-4" data-wow-delay="0.1s" data-wow>
          <div class="bg-primary shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
            <div class="bg-white d-flex align-items-center justify-content-center rounded mb-2"
              style="width: 60px; height: 60px;">
              <i class="fa fa-users text-primary"></i>
            </div>
            <div class="ps-4">
              <h5 class="text-white mb-0">Inversionistas</h5>
              <h1 id="investor-counter" class="text-white mb-0">{{ investorCount }}</h1>
            </div>
          </div>
        </div>
        <div class="col-lg-4" data-wow-delay="0.3s">
          <div class="bg-light shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
            <div class="bg-primary d-flex align-items-center justify-content-center rounded mb-2"
              style="width: 60px; height: 60px;">
              <i class="fa fa-check text-white"></i>
            </div>
            <div class="ps-4">
              <h5 class="text-primary mb-0">Proyectos</h5>
              <h1 class="mb-0" data-toggle="counter-up">143</h1>
            </div>
          </div>
        </div>
        <div class="col-lg-4" data-wow-delay="0.6s">
          <div class="bg-primary shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
            <div class="bg-white d-flex align-items-center justify-content-center rounded mb-2"
              style="width: 60px; height: 60px;">
              <i class="fa fa-award text-primary"></i>
            </div>
            <div class="ps-4">
              <h5 class="text-white mb-0">Rendimiento del %</h5>
              <h1 class="text-white mb-0" data-toggle="counter-up">100</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Facts Start -->


  <!-- About Start -->
  <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
      <div class="row g-5">
        <!-- Columna izquierda: Quiénes somos -->
        <div class="col-lg-7">
          <div class="section-title position-relative pb-3 mb-5">
            <h5 class="fw-bold text-primary text-uppercase">¿Quiénes somos?</h5>
            <h1 class="mb-0">Una empresa dedicada a generar rentabilidades con la inversión de capital a proyectos.</h1>
          </div>
          <p class="mb-4">
            Somos una organización comprometida con la transparencia, la innovación y el crecimiento sostenible.
            Buscamos conectar capital con proyectos sólidos que generen valor y confianza.
          </p>

          <!-- Visión -->
          <div class="wow zoomIn mb-4" data-wow-delay="0.2s">
            <div class="p-4 bg-light rounded shadow-sm">
              <h4 class="text-primary mb-3"><i class="fa fa-eye me-2"></i>Visión</h4>
              <p class="mb-0">
                Ser el líder global en la integración de minería, energía y tecnología, redefiniendo la gestión de
                recursos. Buscamos ser un referente en eficiencia, innovación y sostenibilidad, generando máximo valor
                con mínimo impacto ambiental para impulsar un desarrollo con prosperidad compartida y un futuro más
                inclusivo y responsable.
              </p>
            </div>
          </div>

          <!-- Misión -->
          <div class="wow zoomIn mb-4" data-wow-delay="0.4s">
            <div class="p-4 bg-light rounded shadow-sm">
              <h4 class="text-primary mb-3"><i class="fa fa-bullseye me-2"></i>Misión</h4>
              <p class="mb-0">
                Extraemos y transformamos recursos de manera inteligente, eficiente y responsable con prácticas
                innovadoras para descarbonizar y reducir el impacto ambiental. Nos comprometemos a generar valor
                sostenible para accionistas, comunidades y el entorno, equilibrando desarrollo y bienestar para un
                futuro más limpio y sostenible.
              </p>
            </div>
          </div>

          <!-- CTA con contacto -->
          <div class="d-flex align-items-center mt-5 wow fadeIn" data-wow-delay="0.6s">
            <div class="bg-primary d-flex align-items-center justify-content-center rounded"
              style="width: 60px; height: 60px;">
              <i class="fa fa-phone-alt text-white"></i>
            </div>
            <div class="ps-4">
              <h5 class="mb-2">Habla con un asesor</h5>
              <h4 class="text-primary mb-0">+012 345 6789</h4>
            </div>
          </div>
        </div>

        <!-- Columna derecha: Imagen -->
        <div class="col-lg-5" style="min-height: 500px;">
          <div class="position-relative h-100">
            <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.9s" src="img/about.jpg"
              style="object-fit: cover;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- About End -->


  <!-- Features Start -->
  <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
      <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
        <h5 class="fw-bold text-primary text-uppercase">¿Por qué escogernos?</h5>
        <h1 class="mb-0">Estamos para hacer crecer tu capital exponencialmente</h1>
      </div>
      <div class="row g-5">
        <div class="col-lg-4">
          <div class="row g-5">
            <div class="col-12 wow zoomIn" data-wow-delay="0.2s">
              <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3"
                style="width: 60px; height: 60px;">
                <i class="fa fa-cubes text-white"></i>
              </div>
              <h4>Las mejores industrias</h4>
              <p class="mb-0">Combinamos innovación, sostenibilidad y eficiencia en minería, energía y tecnología de
                forma responsable.</p>
            </div>
            <div class="col-12 wow zoomIn" data-wow-delay="0.6s">
              <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3"
                style="width: 60px; height: 60px;">
                <i class="fa fa-award text-white"></i>
              </div>
              <h4>Inversionistas Sastifechos</h4>
              <p class="mb-0">Generamos confianza y resultados con procesos responsables, innovación, sostenibilidad y
                transparencia.</p>
            </div>
          </div>
        </div>
        <div class="col-lg-4  wow zoomIn" data-wow-delay="0.9s" style="min-height: 350px;">
          <div class="position-relative h-100">
            <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.1s" src="img/feature.jpg"
              style="object-fit: cover;">
          </div>
        </div>
        <div class="col-lg-4">
          <div class="row g-5">
            <div class="col-12 wow zoomIn" data-wow-delay="0.4s">
              <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3"
                style="width: 60px; height: 60px;">
                <i class="fa fa-users-cog text-white"></i>
              </div>
              <h4>Equipo de Profesionales</h4>
              <p class="mb-0">Nuestro equipo experto en finanzas con enfoque en minería, energía y tecnología garantiza
                procesos eficientes, seguros y sostenibles</p>
            </div>
            <div class="col-12 wow zoomIn" data-wow-delay="0.8s">
              <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3"
                style="width: 60px; height: 60px;">
                <i class="fa fa-phone-alt text-white"></i>
              </div>
              <h4>Transparecia y Legalidad</h4>
              <p class="mb-0">Nuestra gestión se basa en ética, transparencia y riguroso cumplimiento para generar
                confianza.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Features Start -->


  <!-- Service Start -->
  <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
      <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
        <h5 class="fw-bold text-primary text-uppercase">Algunos de nuestros proyectos</h5>
        <h1 class="mb-0">Alta rentabilidad y seguridad</h1>
      </div>
      <div class="row g-5">

        <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="0.6s">
          <div
            class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
            <div class="service-icon">
              <i class="fa fa-chart-pie text-white"></i>
            </div>
            <h4 class="mb-3">Minería</h4>
            <p class="m-0">Nuestro modelo de minería 5.0 integra IA, robótica y IoT para optimizar la extracción,
              reducir costos y maximizar la recuperación de minerales críticos.</p>
            <a class="btn btn-lg btn-primary rounded" href="">
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="0.9s">
          <div
            class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
            <div class="service-icon">
              <i class="fa fa-code text-white"></i>
            </div>
            <h4 class="mb-3">Energías Renovables</h4>
            <p class="m-0">Usamos energías limpias para nuestras minas, reduciendo costos y huella de carbono.
              Comercializamos el excedente, diversificando ingresos y apoyando la descarbonización.</p>
            <a class="btn btn-lg btn-primary rounded" href="">
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>


        <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="0.6s">
          <div
            class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
            <div class="service-icon">
              <i class="fa fa-search text-white"></i>
            </div>
            <h4 class="mb-3">Tecnologías</h4>
            <p class="m-0">La innovación es nuestro motor. Desarrollamos y vendemos tecnología propia (IA, IoT,
              robótica) para optimizar minería y energía, mejorar seguridad y generar ingresos.</p>
            <a class="btn btn-lg btn-primary rounded" href="">
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Service End -->


  <!-- Pricing Plan Start -->
  <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
      <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
        <h5 class="fw-bold text-primary text-uppercase">Planes de Inversión</h5>
        <h1 class="mb-0">2 planes de inversión de acuerdo a tus necesidades</h1>
      </div>
      <div class="row g-4 justify-content-center">
        <div class="col-lg-4 wow slideInUp" data-wow-delay="0.6s">
          <div class="bg-light rounded">
            <div class="border-bottom py-4 px-5 mb-4">
              <h4 class="text-primary mb-1">Plan Básico</h4>
              <small class="text-uppercase">Para pequeñas inversiones</small>
            </div>
            <div class="p-5 pt-0">
              <h1 class="display-5 mb-3">
                <small class="align-top" style="font-size: 22px; line-height: 45px;">Desde</small>200k<small
                  class="align-bottom" style="font-size: 16px; line-height: 40px;">/ COP</small>
              </h1>
              <div class="d-flex justify-content-between mb-3"><span>Pagos Quincenales</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
              <div class="d-flex justify-content-between mb-3"><span>Garantía</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
              <div class="d-flex justify-content-between mb-3"><span>Liquidez</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 wow slideInUp" data-wow-delay="0.9s">
          <div class="bg-light rounded">
            <div class="border-bottom py-4 px-5 mb-4">
              <h4 class="text-primary mb-1">Plan Premium</h4>
              <small class="text-uppercase">Para grandes inversiones</small>
            </div>
            <div class="p-5 pt-0">
              <h1 class="display-5 mb-3">
                <small class="align-top" style="font-size: 22px; line-height: 45px;">Desde</small>5M<small
                  class="align-bottom" style="font-size: 16px; line-height: 40px;">/ COP</small>
              </h1>
              <div class="d-flex justify-content-between mb-3"><span>Pagos Mensuales</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
              <div class="d-flex justify-content-between mb-3"><span>Garantía</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
              <div class="d-flex justify-content-between mb-3"><span>Liquidez</span><i
                  class="fa fa-check text-primary pt-1"></i></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Pricing Plan End -->


  <!-- Quote Start -->
















  <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
      <div class="row g-5">

        <div class="col-lg-7">
          <div class="section-title position-relative pb-3 mb-5">
            <h5 class="fw-bold text-primary text-uppercase">Simulador Virtual</h5>
            <h1 class="mb-0">¿No sabes cómo se moverá tu capital? ¡Simula aquí!</h1>
          </div>
          <p class="mb-4">
            ¿Quieres ver el potencial de tu dinero sin arriesgar nada? Nuestro simulador de inversiones es la
            herramienta perfecta para ti.
            Diseñado para ayudarte a visualizar cómo tu capital inicial puede multiplicarse con el tiempo,
            ofreciéndote diferentes modalidades de contrato para que elijas la que mejor se adapte a tus metas.
          </p>
          <div class="row g-0 mb-3">
            <div class="col-sm-6 wow zoomIn" data-wow-delay="0.2s">
              <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Planes Flexibles</h5>
              <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Cálculos Transparentes</h5>
            </div>
            <div class="col-sm-6 wow zoomIn" data-wow-delay="0.4s">
              <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Proyecciones Claras</h5>
              <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Decisiones Informadas</h5>
            </div>
          </div>
          <div class="d-flex align-items-center mt-2 wow zoomIn" data-wow-delay="0.6s">
            <div class="bg-primary d-flex align-items-center justify-content-center rounded"
              style="width: 60px; height: 60px;">
              <i class="fa fa-phone-alt text-white"></i>
            </div>
            <div class="ps-4">
              <h5 class="mb-2">¿Tienes dudas? Escríbenos</h5>
              <h4 class="text-primary mb-0">+57 300 123 4567</h4>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="bg-primary rounded h-100 d-flex align-items-center p-5 wow zoomIn" data-wow-delay="0.9s">
            <form @submit.prevent class="w-100">
              <div class="row g-3">
                <h4 class="text-white mb-3 text-center">Simulador Virtual</h4>

                <div class="col-12">
                  <select v-model="form.plan_id" class="form-select bg-light border-0" style="height: 55px;">
                    <option value="">-- Selecciona un Plan --</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                      {{ plan.name }}
                    </option>
                  </select>
                </div>

                <div class="col-12">
                  <select v-model="form.investment_contract_type" class="form-select bg-light border-0"
                    style="height: 55px;">
                    <option value="abierta">Contrato Abierto</option>
                    <option value="cerrada">Contrato Cerrado</option>
                  </select>
                </div>

                <div class="col-12">
                  <input v-model="form.amount" type="number" class="form-control bg-light border-0"
                    placeholder="Monto a simular (Ej: 200000)" style="height: 55px;">
                </div>

                <div v-if="calculatedPayments.length > 0" class="col-12 mt-3">
                  <ul class="list-group">
                    <li v-for="(payment, index) in calculatedPayments" :key="index"
                      class="list-group-item d-flex justify-content-between align-items-center bg-light border-0 text-dark mb-2 rounded p-3">
                      <span class="fw-bold">{{ payment.label }}</span>
                      <strong class="fs-5">{{ payment.formattedValue }}</strong>
                    </li>
                  </ul>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Quote End -->


  <!-- Testimonial Start -->

  <!-- Testimonial End -->

  <!-- Footer Start -->
  <div class="container-fluid bg-dark text-light mt-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
      <div class="row gx-5">
        <div class="col-lg-4 col-md-6 footer-about">
          <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 bg-primary p-4">
            <a href="#" class="navbar-brand p-0 d-flex align-items-center">

              <h1 class="m-0" style="font-size: 32px; display: flex; align-items: baseline; gap: 6px; color: white;">
                EON
                <small style="font-size: 20px; font-weight: normal;">Grupo Empresarial</small>
              </h1>
            </a>
            <p class="mt-3 mb-4">Somos una organización comprometida con la transparencia, la innovación y el
              crecimiento sostenible. Buscamos conectar capital con proyectos sólidos que generen valor y confianza.</p>
            <form action="">
              <div class="input-group">
                <Link :href="route('register')" as="button" class="btn btn-dark">
                Registrate
                </Link>
              </div>
            </form>
          </div>
        </div>
        <div class="col-lg-8 col-md-6">
          <div class="row gx-5">
            <div class="col-lg-4 col-md-12 pt-5 mb-5">
              <div class="section-title section-title-sm position-relative pb-3 mb-4">
                <h3 class="mb-0" style="color: #444444;">Conocenos</h3>
              </div>
              <div class="d-flex mb-2">
                <i class="bi bi-geo-alt text-primary me-2" style="color: #444444;"></i>
                <p class="mb-0" style="color: #444444;">Calle 123, Bogotá DC, Colombia</p>
              </div>
              <div class="d-flex mb-2">
                <i class="bi bi-envelope-open text-primary me-2"></i>
                <p class="mb-0" style="color: #444444;">soporte@grupoeon.com</p>
              </div>
              <div class="d-flex mb-2">
                <i class="bi bi-telephone text-primary me-2"></i>
                <p class="mb-0" style="color: #444444;">+012 345 67890</p>
              </div>
              <div class="d-flex mt-4">
                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                <a class="btn btn-primary btn-square" href="#"><i class="fab fa-instagram fw-normal"></i></a>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container-fluid text-white" style="background: rgba(240, 69, 38, 0.7);">
    <div class="container text-center">
      <div class="row justify-content-end">
        <div class="col-lg-8 col-md-6">
          <div class="d-flex align-items-center justify-content-center" style="height: 75px;">
            <p class="mb-0">&copy; <a class="text-white border-bottom" href="#">EON Grupo Empresarial</a>. Todos los
              derechos
              reservados.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Footer End -->


  <!-- Back to Top -->
  <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>
</template>

<style scoped>
.navbar-brand-responsive {
  align-items: center;
  /* Asegura la alineación vertical */
  gap: 15px;
  /* ESTA LÍNEA AÑADE LA SEPARACIÓN */
}

.logo-container {
  background-color: white;
  border-radius: 12px;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.logo-container img {
  width: 60px;
  height: 60px;
}

.logo-text h1 {
  font-size: 32px;
  display: flex;
  align-items: baseline;
  gap: 6px;
  /* Espacio entre 'EON' y 'Grupo Empresarial' */
  white-space: nowrap;
  /* Evita que el texto se parta en dos líneas */
}

.logo-text h1 small {
  font-size: 20px;
  font-weight: normal;
}

/* --- MEDIA QUERY PARA MÓVILES --- */
/* Estos estilos se aplican SOLO en pantallas con un ancho MÁXIMO de 768px */
@media (max-width: 768px) {

  /* Hacemos más pequeño el logo en móvil */
  .logo-container {
    padding: 6px;
  }

  .logo-container img {
    width: 45px;
    height: 45px;
  }

  /* Reducimos el tamaño de la fuente para que quepa mejor */
  .logo-text h1 {
    font-size: 24px;
    gap: 4px;
    /* Reducimos el espacio */
  }

  .logo-text h1 small {
    font-size: 16px;
  }

  /* Ajustamos el espaciado general de la barra de navegación en móviles */
  .navbar.px-5 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>