export type AppPlatform =
    | 'web'
    | 'android'
    | 'windows'
    | 'ios'
    | 'macos'
    | 'linux';

export type ProductStatus = 'draft' | 'published' | 'archived';

export type OrderStatus = 'pending' | 'paid' | 'refunded' | 'cancelled';

export type Seller = {
    id: number | null;
    name: string;
};

export type Tag = {
    id: number;
    name: string;
    slug: string;
};

export type ProductFeature = {
    id: number;
    title: string;
    description: string | null;
};

export type ProductScreenshot = {
    id: number;
    url: string;
    caption: string | null;
};

export type Product = {
    id: number;
    user_id: number;
    name: string;
    slug: string;
    tagline: string;
    description: string;
    platform: AppPlatform;
    platform_label: string;
    platform_icon: string;
    status: ProductStatus;
    status_label: string;
    price: number;
    price_formatted: string;
    is_free: boolean;
    currency: string;
    version: string | null;
    requirements: string | null;
    cover_image_url: string | null;
    demo_url: string | null;
    download_url: string | null;
    repository_url: string | null;
    views: number;
    sales: number;
    archived: boolean;
    archived_at: string | null;
    published_at: string | null;
    created_at: string | null;
    seller: Seller;
    tags?: Tag[];
    features?: ProductFeature[];
    screenshots?: ProductScreenshot[];
};

export type License = {
    id: number;
    order_id: number;
    product_id: number;
    key: string;
    downloads: number;
    activated_at: string | null;
    revoked: boolean;
    revoked_at: string | null;
};

export type Order = {
    id: number;
    user_id: number;
    product_id: number;
    reference: string;
    amount: number;
    amount_formatted: string;
    currency: string;
    status: OrderStatus;
    status_label: string;
    paid_at: string | null;
    refunded: boolean;
    refunded_at: string | null;
    created_at: string | null;
    customer: Seller;
    product?: Product;
    license?: License | null;
};

export type Page = {
    id: number;
    slug: string;
    title: string;
    format: 'html' | 'markdown';
    content: string | null;
    rendered_content: string;
    location: string;
    sort_order: number;
    published: boolean;
    published_at: string | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    path: string;
    first_page_url: string | null;
    last_page_url: string | null;
    next_page_url: string | null;
    prev_page_url: string | null;
};
