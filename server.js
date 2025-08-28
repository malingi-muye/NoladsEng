const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

// Import database and models
const { initializeDatabase } = require('./config/database');
const Service = require('./models/Service');
const Product = require('./models/Product');
const Company = require('./models/Company');
const { seedDatabase } = require('./scripts/seedData');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('.'));

// Database connection status
let isDatabaseConnected = false;

// Mock data fallback
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

const mockCompanyInfo = {
  name: "Nolads Engineering",
  description: "Leading electrical engineering services for industrial applications",
  established: 2012,
  specialties: ["Power Systems", "Safety Solutions", "Automation", "Performance Monitoring"]
};

// Database initialization
async function initializeApp() {
  try {
    await initializeDatabase();
    await seedDatabase();
    isDatabaseConnected = true;
    console.log('✅ Application initialized with database successfully');
  } catch (error) {
    console.warn('⚠️  Database connection failed, falling back to mock data:', error.message);
    isDatabaseConnected = false;
    console.log('✅ Application initialized with mock data fallback');
  }
}

// Error handling middleware
function asyncHandler(fn) {
  return (req, res, next) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
}

function errorHandler(err, req, res, next) {
  console.error('API Error:', err);
  res.status(500).json({
    success: false,
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'development' ? err.message : 'Something went wrong'
  });
}

// Helper function to handle database fallback
function handleDatabaseFallback(mockData, filter = {}) {
  let data = [...mockData];

  if (filter.featured === true) {
    data = data.filter(item => item.featured);
  }

  if (filter.active === true) {
    data = data.filter(item => item.active);
  }

  if (filter.category) {
    data = data.filter(item => item.category === filter.category);
  }

  if (filter.limit) {
    data = data.slice(0, filter.limit);
  }

  return data;
}

// API Routes
app.get('/api/services', asyncHandler(async (req, res) => {
  const { featured, active, limit, page } = req.query;

  if (!isDatabaseConnected) {
    const options = {};
    if (featured === 'true') options.featured = true;
    if (active === 'true') options.active = true;
    if (limit) options.limit = parseInt(limit);

    const services = handleDatabaseFallback(mockServices, options);

    return res.json({
      success: true,
      data: services,
      total: services.length,
      source: 'mock'
    });
  }

  const options = {};
  if (featured === 'true') options.featured = true;
  if (active === 'true') options.active = true;
  if (limit) options.limit = parseInt(limit);
  if (page) options.page = parseInt(page);

  const services = await Service.getAll(options);
  const total = await Service.getCount(options);

  res.json({
    success: true,
    data: services,
    total,
    source: 'database'
  });
}));

app.get('/api/services/featured', asyncHandler(async (req, res) => {
  const { limit } = req.query;

  if (!isDatabaseConnected) {
    const services = handleDatabaseFallback(mockServices, {
      featured: true,
      limit: limit ? parseInt(limit) : null
    });

    return res.json({
      success: true,
      data: services,
      total: services.length,
      source: 'mock'
    });
  }

  const services = await Service.getFeatured(limit ? parseInt(limit) : null);

  res.json({
    success: true,
    data: services,
    total: services.length,
    source: 'database'
  });
}));

app.get('/api/products', asyncHandler(async (req, res) => {
  const { featured, limit, page, category } = req.query;

  if (!isDatabaseConnected) {
    const options = {};
    if (featured === 'true') options.featured = true;
    if (limit) options.limit = parseInt(limit);
    if (category) options.category = category;

    const products = handleDatabaseFallback(mockProducts, options);

    return res.json({
      success: true,
      data: products,
      total: products.length,
      source: 'mock'
    });
  }

  const options = {};
  if (featured === 'true') options.featured = true;
  if (limit) options.limit = parseInt(limit);
  if (page) options.page = parseInt(page);
  if (category) options.category = category;

  const products = await Product.getAll(options);
  const total = await Product.getCount(options);

  res.json({
    success: true,
    data: products,
    total,
    source: 'database'
  });
}));

app.get('/api/products/featured', asyncHandler(async (req, res) => {
  const { limit } = req.query;

  if (!isDatabaseConnected) {
    const products = handleDatabaseFallback(mockProducts, {
      featured: true,
      limit: limit ? parseInt(limit) : null
    });

    return res.json({
      success: true,
      data: products,
      total: products.length,
      source: 'mock'
    });
  }

  const products = await Product.getFeatured(limit ? parseInt(limit) : null);

  res.json({
    success: true,
    data: products,
    total: products.length,
    source: 'database'
  });
}));

app.get('/api/products/categories', asyncHandler(async (req, res) => {
  if (!isDatabaseConnected) {
    const categories = [...new Set(mockProducts.map(p => p.category))].sort();

    return res.json({
      success: true,
      data: categories,
      source: 'mock'
    });
  }

  const categories = await Product.getCategories();

  res.json({
    success: true,
    data: categories,
    source: 'database'
  });
}));

app.get('/api/company/stats', asyncHandler(async (req, res) => {
  if (!isDatabaseConnected) {
    return res.json({
      success: true,
      data: mockCompanyStats,
      source: 'mock'
    });
  }

  const stats = await Company.getStats();

  res.json({
    success: true,
    data: stats,
    source: 'database'
  });
}));

app.get('/api/company/info', asyncHandler(async (req, res) => {
  if (!isDatabaseConnected) {
    return res.json({
      success: true,
      data: mockCompanyInfo,
      source: 'mock'
    });
  }

  const info = await Company.getInfo();

  if (!info) {
    return res.json({
      success: true,
      data: mockCompanyInfo,
      source: 'mock_fallback'
    });
  }

  // Parse specialties JSON if it's a string
  if (typeof info.specialties === 'string') {
    info.specialties = JSON.parse(info.specialties);
  }

  res.json({
    success: true,
    data: info,
    source: 'database'
  });
}));

// Additional API endpoints
app.get('/api/services/:id', asyncHandler(async (req, res) => {
  if (!isDatabaseConnected) {
    const service = mockServices.find(s => s.id === parseInt(req.params.id));

    if (!service) {
      return res.status(404).json({
        success: false,
        error: 'Service not found'
      });
    }

    return res.json({
      success: true,
      data: service,
      source: 'mock'
    });
  }

  const service = await Service.getById(req.params.id);

  if (!service) {
    return res.status(404).json({
      success: false,
      error: 'Service not found'
    });
  }

  res.json({
    success: true,
    data: service,
    source: 'database'
  });
}));

app.get('/api/products/:id', asyncHandler(async (req, res) => {
  if (!isDatabaseConnected) {
    const product = mockProducts.find(p => p.id === parseInt(req.params.id));

    if (!product) {
      return res.status(404).json({
        success: false,
        error: 'Product not found'
      });
    }

    return res.json({
      success: true,
      data: product,
      source: 'mock'
    });
  }

  const product = await Product.getById(req.params.id);

  if (!product) {
    return res.status(404).json({
      success: false,
      error: 'Product not found'
    });
  }

  res.json({
    success: true,
    data: product,
    source: 'database'
  });
}));

// Error handling middleware
app.use(errorHandler);

// Catch-all for SPA routing
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

// API status endpoint
app.get('/api/status', (req, res) => {
  res.json({
    success: true,
    status: 'running',
    database: isDatabaseConnected ? 'connected' : 'disconnected',
    dataSource: isDatabaseConnected ? 'database' : 'mock'
  });
});

// Initialize and start server
async function startServer() {
  await initializeApp();

  app.listen(PORT, () => {
    console.log(`🚀 Server running on port ${PORT}`);
    console.log(`📱 Frontend: http://localhost:${PORT}`);
    console.log(`🔌 API: http://localhost:${PORT}/api`);
    console.log(`💾 Data source: ${isDatabaseConnected ? 'Database' : 'Mock data'}`);
    if (!isDatabaseConnected) {
      console.log('💡 To use a real database, connect to Neon: https://console.neon.tech');
    }
  });
}

startServer().catch(error => {
  console.error('❌ Failed to start server:', error);
  process.exit(1);
});
