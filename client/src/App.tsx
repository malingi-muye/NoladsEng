import React from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { Toaster } from '@/components/ui/sonner'
import ModernNavBar from '@/components/ModernNavBar'
import PageTransitionWrapper from '@/components/PageTransitionWrapper'
import Index from '@/pages/Index'

// Placeholder components for other routes
const ServicesPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Services</h1>
      <p className="text-gray-600">Our comprehensive engineering services</p>
    </div>
  </div>
)

const ProductsPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Products</h1>
      <p className="text-gray-600">Our industrial engineering products</p>
    </div>
  </div>
)

const BlogPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Blog</h1>
      <p className="text-gray-600">Latest insights and industry news</p>
    </div>
  </div>
)

const AboutPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">About Us</h1>
      <p className="text-gray-600">Learn more about Nolads Engineering</p>
    </div>
  </div>
)

const ContactPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Contact Us</h1>
      <p className="text-gray-600">Get in touch with our team</p>
    </div>
  </div>
)

const AdminPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Admin Page</h1>
      <p className="text-gray-600">Administrative interface</p>
    </div>
  </div>
)

const QuotePage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">Request Quote</h1>
      <p className="text-gray-600">Get a custom quote for your project</p>
    </div>
  </div>
)

const NotFoundPage = () => (
  <div className="min-h-screen pt-20 flex items-center justify-center">
    <div className="text-center">
      <h1 className="text-6xl font-bold text-gray-900 mb-4">404</h1>
      <h2 className="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
      <p className="text-gray-600">The page you're looking for doesn't exist.</p>
    </div>
  </div>
)

function App() {
  return (
    <Router>
      <div className="App">
        <ModernNavBar />
        
        <PageTransitionWrapper>
          <Routes>
            <Route path="/" element={<Index />} />
            <Route path="/services" element={<ServicesPage />} />
            <Route path="/services/:id" element={<ServicesPage />} />
            <Route path="/products" element={<ProductsPage />} />
            <Route path="/products/:id" element={<ProductsPage />} />
            <Route path="/blog" element={<BlogPage />} />
            <Route path="/about" element={<AboutPage />} />
            <Route path="/contact" element={<ContactPage />} />
            <Route path="/admin" element={<AdminPage />} />
            <Route path="/quote" element={<QuotePage />} />
            <Route path="/portfolio" element={<ServicesPage />} />
            <Route path="*" element={<NotFoundPage />} />
          </Routes>
        </PageTransitionWrapper>

        <Toaster />
      </div>
    </Router>
  )
}

export default App
