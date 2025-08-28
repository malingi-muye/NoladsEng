import * as React from "react"
import { motion } from "framer-motion"
import { cn } from "@/lib/utils"

const ModernCard = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & {
    hover?: boolean
    gradient?: boolean
  }
>(({ className, hover = true, gradient = false, ...props }, ref) => (
  <motion.div
    ref={ref}
    className={cn(
      "rounded-xl border bg-card text-card-foreground shadow-sm",
      gradient && "bg-gradient-to-br from-card to-card/80",
      className
    )}
    whileHover={hover ? { y: -4, scale: 1.02 } : {}}
    transition={{ type: "spring", stiffness: 300, damping: 20 }}
    {...props}
  />
))
ModernCard.displayName = "ModernCard"

const ModernCardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-6", className)}
    {...props}
  />
))
ModernCardHeader.displayName = "ModernCardHeader"

const ModernCardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn(
      "text-2xl font-semibold leading-none tracking-tight",
      className
    )}
    {...props}
  />
))
ModernCardTitle.displayName = "ModernCardTitle"

const ModernCardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-sm text-muted-foreground", className)}
    {...props}
  />
))
ModernCardDescription.displayName = "ModernCardDescription"

const ModernCardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-6 pt-0", className)} {...props} />
))
ModernCardContent.displayName = "ModernCardContent"

const ModernCardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center p-6 pt-0", className)}
    {...props}
  />
))
ModernCardFooter.displayName = "ModernCardFooter"

// Service Card Component
interface ServiceCardProps {
  service: {
    id: number
    title: string
    description: string
    category: string
    image: string
  }
  className?: string
}

const ServiceCard = React.forwardRef<
  HTMLDivElement,
  ServiceCardProps
>(({ service, className }, ref) => (
  <ModernCard ref={ref} className={cn("overflow-hidden group", className)}>
    <div className="relative h-48 overflow-hidden">
      <img 
        src={service.image} 
        alt={service.title}
        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
      />
      <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
      <div className="absolute bottom-4 left-4 text-white">
        <span className="inline-block px-2 py-1 text-xs font-medium bg-yellow-500 text-black rounded-full mb-2">
          {service.category}
        </span>
      </div>
    </div>
    <ModernCardHeader>
      <ModernCardTitle className="text-xl">{service.title}</ModernCardTitle>
      <ModernCardDescription className="text-base">
        {service.description}
      </ModernCardDescription>
    </ModernCardHeader>
  </ModernCard>
))
ServiceCard.displayName = "ServiceCard"

// Product Card Component
interface ProductCardProps {
  product: {
    id: number
    name: string
    description: string
    category: string
    price: number
    image: string
  }
  className?: string
}

const ProductCard = React.forwardRef<
  HTMLDivElement,
  ProductCardProps
>(({ product, className }, ref) => (
  <ModernCard ref={ref} className={cn("overflow-hidden group", className)}>
    <div className="relative h-48 overflow-hidden">
      <img 
        src={product.image} 
        alt={product.name}
        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
      />
      <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
      <div className="absolute top-4 right-4 text-white">
        <span className="inline-block px-3 py-1 text-sm font-bold bg-blue-600 rounded-full">
          ${product.price.toLocaleString()}
        </span>
      </div>
      <div className="absolute bottom-4 left-4 text-white">
        <span className="inline-block px-2 py-1 text-xs font-medium bg-yellow-500 text-black rounded-full">
          {product.category}
        </span>
      </div>
    </div>
    <ModernCardHeader>
      <ModernCardTitle className="text-xl">{product.name}</ModernCardTitle>
      <ModernCardDescription className="text-base">
        {product.description}
      </ModernCardDescription>
    </ModernCardHeader>
  </ModernCard>
))
ProductCard.displayName = "ProductCard"

export {
  ModernCard,
  ModernCardHeader,
  ModernCardFooter,
  ModernCardTitle,
  ModernCardDescription,
  ModernCardContent,
  ServiceCard,
  ProductCard,
}
