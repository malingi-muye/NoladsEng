import React, { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import { Link } from 'react-router-dom'
import { ArrowRight, Star, Quote } from 'lucide-react'
import ModernHeroSection from '@/components/ModernHeroSection'
import { ModernButton } from '@/components/ui/modern-button'
import { ServiceCard, ProductCard } from '@/components/ui/modern-card'
import { api, Service, Product } from '@/lib/api'

const Index: React.FC = () => {
  const [featuredServices, setFeaturedServices] = useState<Service[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchFeaturedContent = async () => {
      try {
        const [servicesResponse, productsResponse] = await Promise.all([
          api.services.getFeatured(4),
          api.products.getFeatured(4)
        ])

        if (servicesResponse.success) {
          setFeaturedServices(servicesResponse.data)
        }

        if (productsResponse.success) {
          setFeaturedProducts(productsResponse.data)
        }
      } catch (error) {
        console.error('Failed to fetch featured content:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchFeaturedContent()
  }, [])

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2
      }
    }
  }

  const itemVariants = {
    hidden: { y: 50, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        duration: 0.6,
        ease: "easeOut"
      }
    }
  }

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <ModernHeroSection />

      {/* Featured Services Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
            className="text-center mb-16"
          >
            <motion.div variants={itemVariants}>
              <h2 className="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Our <span className="gradient-text">Services</span>
              </h2>
              <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                Comprehensive engineering solutions designed to power your industrial operations 
                with precision, safety, and innovation.
              </p>
            </motion.div>
          </motion.div>

          {loading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {[...Array(4)].map((_, index) => (
                <div key={index} className="animate-pulse">
                  <div className="bg-gray-300 h-48 rounded-xl mb-4" />
                  <div className="bg-gray-300 h-6 rounded mb-2" />
                  <div className="bg-gray-300 h-4 rounded" />
                </div>
              ))}
            </div>
          ) : (
            <motion.div
              variants={containerVariants}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true }}
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12"
            >
              {featuredServices.map((service) => (
                <motion.div key={service.id} variants={itemVariants}>
                  <Link to={`/services/${service.id}`}>
                    <ServiceCard service={service} />
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          )}

          <motion.div
            variants={itemVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="text-center"
          >
            <ModernButton
              variant="gradientBlue"
              size="lg"
              asChild
            >
              <Link to="/services" className="inline-flex items-center space-x-2">
                <span>View All Services</span>
                <ArrowRight className="w-4 h-4" />
              </Link>
            </ModernButton>
          </motion.div>
        </div>
      </section>

      {/* Featured Products Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
            className="text-center mb-16"
          >
            <motion.div variants={itemVariants}>
              <h2 className="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Featured <span className="gradient-text">Products</span>
              </h2>
              <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                Cutting-edge equipment and solutions engineered for optimal performance 
                in demanding industrial environments.
              </p>
            </motion.div>
          </motion.div>

          {loading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {[...Array(4)].map((_, index) => (
                <div key={index} className="animate-pulse">
                  <div className="bg-gray-300 h-48 rounded-xl mb-4" />
                  <div className="bg-gray-300 h-6 rounded mb-2" />
                  <div className="bg-gray-300 h-4 rounded" />
                </div>
              ))}
            </div>
          ) : (
            <motion.div
              variants={containerVariants}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true }}
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12"
            >
              {featuredProducts.map((product) => (
                <motion.div key={product.id} variants={itemVariants}>
                  <Link to={`/products/${product.id}`}>
                    <ProductCard product={product} />
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          )}

          <motion.div
            variants={itemVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="text-center"
          >
            <ModernButton
              variant="gradientBlue"
              size="lg"
              asChild
            >
              <Link to="/products" className="inline-flex items-center space-x-2">
                <span>View All Products</span>
                <ArrowRight className="w-4 h-4" />
              </Link>
            </ModernButton>
          </motion.div>
        </div>
      </section>

      {/* About Section */}
      <section className="py-20 bg-blue-900 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center"
          >
            <motion.div variants={itemVariants}>
              <h2 className="text-4xl lg:text-5xl font-bold mb-6">
                Why Choose <span className="gradient-text">Nolads Engineering?</span>
              </h2>
              <p className="text-xl text-blue-100 mb-8 leading-relaxed">
                With decades of experience and a commitment to excellence, we deliver 
                engineering solutions that exceed expectations and drive industrial innovation.
              </p>
              
              <div className="space-y-4 mb-8">
                {[
                  "Industry-leading expertise and innovation",
                  "Comprehensive safety and compliance standards", 
                  "24/7 support and maintenance services",
                  "Custom solutions tailored to your needs"
                ].map((feature, index) => (
                  <motion.div
                    key={index}
                    variants={{
                      hidden: { opacity: 0, x: -20 },
                      visible: {
                        opacity: 1,
                        x: 0,
                        transition: { delay: index * 0.1 }
                      }
                    }}
                    className="flex items-center space-x-3"
                  >
                    <Star className="w-5 h-5 text-yellow-400 flex-shrink-0" />
                    <span className="text-blue-100">{feature}</span>
                  </motion.div>
                ))}
              </div>

              <ModernButton
                variant="gradient"
                size="lg"
                asChild
              >
                <Link to="/about">Learn More About Us</Link>
              </ModernButton>
            </motion.div>

            <motion.div variants={itemVariants}>
              <div className="relative">
                <img
                  src="/slider/slide2.jpg"
                  alt="Nolads Engineering Team"
                  className="rounded-2xl shadow-2xl"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-blue-900/60 to-transparent rounded-2xl" />
                
                <div className="absolute bottom-6 left-6 right-6">
                  <div className="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20">
                    <div className="flex items-start space-x-3">
                      <Quote className="w-5 h-5 text-yellow-400 flex-shrink-0 mt-1" />
                      <div>
                        <p className="text-white text-sm mb-2">
                          "Nolads Engineering transformed our facility with their innovative 
                          power distribution system. Outstanding quality and service."
                        </p>
                        <div className="text-xs text-blue-200">
                          - Chief Engineer, Industrial Corp
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gray-900">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <motion.div
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
          >
            <motion.h2 
              variants={itemVariants}
              className="text-4xl lg:text-5xl font-bold text-white mb-6"
            >
              Ready to <span className="gradient-text">Transform</span> Your Operations?
            </motion.h2>
            
            <motion.p 
              variants={itemVariants}
              className="text-xl text-gray-300 mb-8 max-w-2xl mx-auto"
            >
              Get in touch with our engineering experts to discuss your project 
              requirements and discover how we can help you achieve your goals.
            </motion.p>

            <motion.div 
              variants={itemVariants}
              className="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6"
            >
              <ModernButton
                variant="gradient"
                size="xl"
                asChild
              >
                <Link to="/quote">Get Free Quote</Link>
              </ModernButton>
              
              <ModernButton
                variant="outline"
                size="xl"
                className="border-white/30 text-white hover:bg-white hover:text-gray-900"
                asChild
              >
                <Link to="/contact">Contact Us</Link>
              </ModernButton>
            </motion.div>
          </motion.div>
        </div>
      </section>
    </div>
  )
}

export default Index
