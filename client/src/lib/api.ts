const API_BASE = import.meta.env.VITE_API_BASE || '/api';

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  total?: number;
  source?: 'database' | 'mock';
  error?: string;
  message?: string;
}

export interface Service {
  id: number;
  title: string;
  description: string;
  category: string;
  featured: boolean;
  active: boolean;
  image: string;
}

export interface Product {
  id: number;
  name: string;
  description: string;
  category: string;
  featured: boolean;
  price: number;
  image: string;
}

export interface CompanyStats {
  projects_completed: number;
  clients_served: number;
  years_experience: number;
  safety_certifications: number;
}

export interface CompanyInfo {
  name: string;
  description: string;
  established: number;
  specialties: string[];
}

class ApiClient {
  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${API_BASE}${endpoint}`;
    
    const config: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // Services API
  services = {
    getAll: (params?: {
      page?: number;
      limit?: number;
      search?: string;
      category?: string;
      active?: boolean;
    }): Promise<ApiResponse<Service[]>> => {
      const searchParams = new URLSearchParams();
      
      if (params?.page) searchParams.set('page', params.page.toString());
      if (params?.limit) searchParams.set('limit', params.limit.toString());
      if (params?.search) searchParams.set('search', params.search);
      if (params?.category) searchParams.set('category', params.category);
      if (params?.active !== undefined) searchParams.set('active', params.active.toString());
      
      return this.request<Service[]>(`/services?${searchParams}`);
    },

    getFeatured: (limit?: number): Promise<ApiResponse<Service[]>> => {
      const searchParams = new URLSearchParams();
      if (limit) searchParams.set('limit', limit.toString());
      
      return this.request<Service[]>(`/services/featured?${searchParams}`);
    },

    getById: (id: string | number): Promise<ApiResponse<Service>> => {
      return this.request<Service>(`/services/${id}`);
    },
  };

  // Products API
  products = {
    getAll: (params?: {
      page?: number;
      limit?: number;
      category?: string;
      featured?: boolean;
    }): Promise<ApiResponse<Product[]>> => {
      const searchParams = new URLSearchParams();
      
      if (params?.page) searchParams.set('page', params.page.toString());
      if (params?.limit) searchParams.set('limit', params.limit.toString());
      if (params?.category) searchParams.set('category', params.category);
      if (params?.featured !== undefined) searchParams.set('featured', params.featured.toString());
      
      return this.request<Product[]>(`/products?${searchParams}`);
    },

    getFeatured: (limit?: number): Promise<ApiResponse<Product[]>> => {
      const searchParams = new URLSearchParams();
      if (limit) searchParams.set('limit', limit.toString());
      
      return this.request<Product[]>(`/products/featured?${searchParams}`);
    },

    getCategories: (): Promise<ApiResponse<string[]>> => {
      return this.request<string[]>('/products/categories');
    },

    getById: (id: string | number): Promise<ApiResponse<Product>> => {
      return this.request<Product>(`/products/${id}`);
    },
  };

  // Company API
  company = {
    getStats: (): Promise<ApiResponse<CompanyStats>> => {
      return this.request<CompanyStats>('/company/stats');
    },

    getInfo: (): Promise<ApiResponse<CompanyInfo>> => {
      return this.request<CompanyInfo>('/company/info');
    },
  };

  // System API
  status = (): Promise<ApiResponse<{ status: string; database: string; dataSource: string }>> => {
    return this.request('/status');
  };
}

export const api = new ApiClient();
export default api;
