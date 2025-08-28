const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('.'));

// Mock data
const mockServices = [
  {
    id: 1,
    title: "Power Systems Design",
    description: "Custom electrical power distribution systems for industrial applications",
    category: "power",
    featured: true,
    active: true,
    image: "/slider/slide1.jpg"
  },
  {
    id: 2,
    title: "Safety & Compliance",
    description: "Electrical safety audits and compliance certification services",
    category: "safety",
    featured: true,
    active: true,
    image: "/slider/slide2.jpg"
  },
  {
    id: 3,
    title: "Industrial Automation",
    description: "Automated control systems and process optimization",
    category: "automation",
    featured: true,
    active: true,
    image: "/slider/slide3.jpg"
  },
  {
    id: 4,
    title: "Performance Monitoring",
    description: "Real-time system monitoring and predictive maintenance",
    category: "monitoring",
    featured: true,
    active: true,
    image: "/slider/slide4.jpg"
  }
];

const mockProducts = [
  {
    id: 1,
    name: "Power Distribution Panel",
    description: "High-capacity electrical distribution panel for industrial use",
    category: "panels",
    featured: true,
    price: 2500,
    image: "/slider/slide1.jpg"
  },
  {
    id: 2,
    name: "Safety Monitoring System",
    description: "Advanced safety monitoring and alert system",
    category: "safety",
    featured: true,
    price: 3200,
    image: "/slider/slide2.jpg"
  },
  {
    id: 3,
    name: "Automation Controller",
    description: "Industrial automation control unit with IoT capabilities",
    category: "automation",
    featured: true,
    price: 4500,
    image: "/slider/slide3.jpg"
  },
  {
    id: 4,
    name: "Performance Analytics Software",
    description: "Real-time performance monitoring and analytics platform",
    category: "software",
    featured: true,
    price: 1800,
    image: "/slider/slide4.jpg"
  }
];

const mockCompanyStats = {
  projects_completed: 150,
  clients_served: 85,
  years_experience: 12,
  safety_certifications: 8
};

// API Routes
app.get('/api/services', (req, res) => {
  const { featured, active, limit } = req.query;
  let services = [...mockServices];
  
  if (featured === 'true') {
    services = services.filter(s => s.featured);
  }
  
  if (active === 'true') {
    services = services.filter(s => s.active);
  }
  
  if (limit) {
    services = services.slice(0, parseInt(limit));
  }
  
  res.json({
    success: true,
    data: services,
    total: services.length
  });
});

app.get('/api/services/featured', (req, res) => {
  const { limit } = req.query;
  let services = mockServices.filter(s => s.featured);
  
  if (limit) {
    services = services.slice(0, parseInt(limit));
  }
  
  res.json({
    success: true,
    data: services,
    total: services.length
  });
});

app.get('/api/products', (req, res) => {
  const { featured, limit } = req.query;
  let products = [...mockProducts];
  
  if (featured === 'true') {
    products = products.filter(p => p.featured);
  }
  
  if (limit) {
    products = products.slice(0, parseInt(limit));
  }
  
  res.json({
    success: true,
    data: products,
    total: products.length
  });
});

app.get('/api/products/featured', (req, res) => {
  const { limit } = req.query;
  let products = mockProducts.filter(p => p.featured);
  
  if (limit) {
    products = products.slice(0, parseInt(limit));
  }
  
  res.json({
    success: true,
    data: products,
    total: products.length
  });
});

app.get('/api/company/stats', (req, res) => {
  res.json({
    success: true,
    data: mockCompanyStats
  });
});

app.get('/api/company/info', (req, res) => {
  res.json({
    success: true,
    data: {
      name: "Nolads Engineering",
      description: "Leading electrical engineering services for industrial applications",
      established: 2012,
      specialties: ["Power Systems", "Safety Solutions", "Automation", "Performance Monitoring"]
    }
  });
});

// Catch-all for SPA routing
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`Frontend: http://localhost:${PORT}`);
  console.log(`API: http://localhost:${PORT}/api`);
});
