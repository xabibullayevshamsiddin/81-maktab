/**
 * 81-IDUM — Site Boot Loader 3D Animation
 * Three.js WebGL engine: icosahedron + torus rings + particles
 */
(function () {
  'use strict';

  // THREE.js yuklanmaguncha kut
  function init3DLoader() {
    var canvas = document.getElementById('loader-3d-canvas');
    if (!canvas || !window.THREE) return;

    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    var W = 200, H = 200;
    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(55, W / H, 0.1, 100);
    camera.position.z = 5.5;

    var renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);

    // ── 1. MARKAZIY ICOSAHEDRON ──────────────────────────────────────────────
    var coreGeo = new THREE.IcosahedronGeometry(1.1, 1);
    var coreMat = new THREE.MeshPhongMaterial({
      color: isDark ? 0x1e3a8a : 0x1565c0,
      emissive: isDark ? 0x0d3f78 : 0x0d3f78,
      specular: 0x14b8a6,
      shininess: 120,
      transparent: true,
      opacity: 0.75,
    });
    var core = new THREE.Mesh(coreGeo, coreMat);
    scene.add(core);

    // ── 2. WIREFRAME QATLAMI ──────────────────────────────────────────────────
    var wireGeo = new THREE.IcosahedronGeometry(1.15, 1);
    var wireMat = new THREE.MeshBasicMaterial({
      color: 0x14b8a6,
      wireframe: true,
      transparent: true,
      opacity: 0.35,
    });
    var wireframe = new THREE.Mesh(wireGeo, wireMat);
    scene.add(wireframe);

    // ── 3. TORUS HALQALAR (3D orbital rings) ─────────────────────────────────
    function makeTorus(radius, tube, color, rx, ry, rz) {
      var geo = new THREE.TorusGeometry(radius, tube, 20, 120);
      var mat = new THREE.MeshBasicMaterial({
        color: color,
        transparent: true,
        opacity: 0.75,
      });
      var mesh = new THREE.Mesh(geo, mat);
      mesh.rotation.x = rx;
      mesh.rotation.y = ry;
      mesh.rotation.z = rz || 0;
      scene.add(mesh);
      return mesh;
    }

    var torus1 = makeTorus(1.85, 0.028, 0x14b8a6, Math.PI / 2.5, 0);
    var torus2 = makeTorus(1.85, 0.022, 0x3b82f6, 0.3, Math.PI / 4);
    var torus3 = makeTorus(1.85, 0.018, 0xec4899, Math.PI / 6, Math.PI / 2.5);

    // ── 4. ZARRACHALAR (Particle cloud) ──────────────────────────────────────
    var particleCount = 160;
    var positions = new Float32Array(particleCount * 3);
    for (var i = 0; i < particleCount; i++) {
      var theta = Math.random() * Math.PI * 2;
      var phi = Math.acos(2 * Math.random() - 1);
      var r = 2.3 + Math.random() * 1.0;
      positions[i * 3]     = r * Math.sin(phi) * Math.cos(theta);
      positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
      positions[i * 3 + 2] = r * Math.cos(phi);
    }
    var pGeo = new THREE.BufferGeometry();
    pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    var pMat = new THREE.PointsMaterial({
      color: 0x14b8a6,
      size: 0.045,
      transparent: true,
      opacity: 0.9,
    });
    var particles = new THREE.Points(pGeo, pMat);
    scene.add(particles);

    // ── 5. YORUG'LIK ──────────────────────────────────────────────────────────
    var ambient = new THREE.AmbientLight(0x404080, 3);
    scene.add(ambient);

    var light1 = new THREE.PointLight(0x14b8a6, 4, 15);
    light1.position.set(3, 3, 3);
    scene.add(light1);

    var light2 = new THREE.PointLight(0x3b82f6, 3, 15);
    light2.position.set(-3, -2, 2);
    scene.add(light2);

    var light3 = new THREE.PointLight(0xec4899, 2, 10);
    light3.position.set(0, 3, -3);
    scene.add(light3);

    // ── 6. ANIMATSIYA LOOP ───────────────────────────────────────────────────
    var clock = new THREE.Clock();
    var rafId;

    function animate() {
      // Loader tugagan bo'lsa to'xtat
      var loaderEl = document.getElementById('site-boot-loader');
      if (!loaderEl || loaderEl.classList.contains('site-boot-loader--done')) {
        renderer.dispose();
        return;
      }

      rafId = requestAnimationFrame(animate);
      var t = clock.getElapsedTime();

      // Asosiy ob'yektlar aylanishi
      core.rotation.x = t * 0.35;
      core.rotation.y = t * 0.5;
      wireframe.rotation.x = -t * 0.28;
      wireframe.rotation.y = t * 0.42;

      // Torus halqalar mustaqil aylanadi
      torus1.rotation.z = t * 0.7;
      torus2.rotation.z = -t * 0.55;
      torus3.rotation.y = t * 0.45;
      torus3.rotation.x = Math.PI / 6 + t * 0.3;

      // Zarrachalar sekin aylanadi
      particles.rotation.y = t * 0.15;
      particles.rotation.x = t * 0.08;

      // Yorug'liklar harakatlanadi
      light1.position.x = Math.sin(t * 0.8) * 4;
      light1.position.y = Math.cos(t * 0.6) * 4;
      light2.position.x = Math.cos(t * 0.7) * 4;
      light2.position.z = Math.sin(t * 0.9) * 4;

      // Mayinroq "pulsation" (nefas olish) effekti
      var pulse = 1 + Math.sin(t * 2.5) * 0.04;
      core.scale.set(pulse, pulse, pulse);

      renderer.render(scene, camera);
    }

    animate();

    // Dark mode o'zgarganda ranglarni yangilash
    var observer = new MutationObserver(function () {
      var dark = document.documentElement.getAttribute('data-theme') === 'dark';
      coreMat.color.setHex(dark ? 0x1e3a8a : 0x1565c0);
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
  }

  // THREE yuklanmaguncha kut
  if (window.THREE) {
    init3DLoader();
  } else {
    window.addEventListener('THREE_READY', init3DLoader, { once: true });
  }
})();
