<template>
  <div class="page-wrapper">
    <Sidebar currentRoute="products" />

    <main class="main-content">
      <!-- Header -->
      <header class="header">
        <h1>Gerenciamento de Produtos</h1>
        <div class="header-actions">
          <button class="btn-filter" @click="toggleFilters">Filtros</button>
          <button class="btn-create" @click="openModal('create')">
            Novo Produto
          </button>
        </div>
      </header>

      <div class="container">
        <!-- Filtros -->
        <div class="filters-card" :class="{ show: showFilters }">
          <div class="filters-header">
            <h3>Filtros</h3>
            <button class="btn-close-filters" @click="toggleFilters">✕</button>
          </div>
          <div class="filters-grid">
            <div class="filter-group">
              <label>Nome do Produto</label>
              <input
                type="text"
                v-model="filters.title"
                placeholder="Buscar..."
              />
            </div>
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.active">
                <option :value="undefined">Todos</option>
                <option :value="true">Ativos</option>
                <option :value="false">Inativos</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Preço Mínimo</label>
              <input
                type="number"
                v-model.number="filters.price_min"
                step="0.01"
                placeholder="R$ 0,00"
              />
            </div>
            <div class="filter-group">
              <label>Preço Máximo</label>
              <input
                type="number"
                v-model.number="filters.price_max"
                step="0.01"
                placeholder="R$ 0,00"
              />
            </div>
          </div>
          <div class="filters-footer">
            <button class="btn-clear-filters" @click="clearFilters">
              Limpar Filtros
            </button>
            <button class="btn-apply-filters" @click="applyFilters">
              Aplicar Filtros
            </button>
          </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="products-card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Imagem</th>
                  <th>Título</th>
                  <th>Custo</th>
                  <th>Preço de Venda</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in products" :key="product.id">
                  <td>{{ product.id }}</td>
                  <td>
                    <div class="product-image-placeholder">
                      <img
                        v-if="product.images?.length > 0"
                        :src="product.images[0].url"
                        :alt="product.title"
                        class="product-thumb"
                      />
                      <span v-else>📷</span>
                    </div>
                  </td>
                  <td>{{ product.title }}</td>
                  <td>{{ formatCurrency(product.cost) }}</td>
                  <td>{{ formatCurrency(product.price) }}</td>
                  <td>
                    <span
                      class="badge"
                      :class="
                        product.active ? 'badge-active' : 'badge-inactive'
                      "
                    >
                      {{ product.active ? "Ativo" : "Inativo" }}
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button
                        class="btn-edit"
                        @click="openModal('edit', product)"
                      >
                        Editar
                      </button>
                      <button
                        v-if="product.active"
                        class="btn-deactivate"
                        @click="deactivateProduct(product.id)"
                      >
                        Desativar
                      </button>
                      <button
                        v-else
                        class="btn-activate"
                        @click="activateProduct(product.id)"
                      >
                        Ativar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="products.length === 0 && !loading">
                  <td colspan="8" class="empty-state">
                    Nenhum produto encontrado.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <div class="pagination" v-if="pagination.last_page > 1">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="pagination-arrow"
            >
              &lt;
            </button>
            <div class="pagination-status">
              <input
                type="number"
                :value="pagination.current_page"
                @keyup.enter="goToPage($event.target.value)"
                class="pagination-input"
                min="1"
                :max="pagination.last_page"
              />
              <span>/ {{ pagination.last_page }}</span>
            </div>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="pagination-arrow"
            >
              &gt;
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Create/Update -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ isEdit ? "Editar Produto" : "Novo Produto" }}</h2>
          <button class="btn-close-modal" @click="closeModal">✕</button>
        </div>

        <div class="modal-body">
          <!-- Formulário (sem @submit.prevent para evitar conflitos) -->
          <div class="form-content">
            <div class="form-group">
              <label>Título *</label>
              <input
                type="text"
                v-model="form.title"
                placeholder="Ex: Caixão Luxo"
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Custo (R$) *</label>
                <input
                  type="number"
                  v-model.number="form.cost"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  @input="validatePrice"
                />
              </div>
              <div class="form-group">
                <label>Preço de Venda (R$) *</label>
                <input
                  type="number"
                  v-model.number="form.price"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  :class="{ 'input-error': priceError }"
                  @input="validatePrice"
                />
                <small v-if="priceError" class="error-message">{{
                  priceError
                }}</small>
              </div>
            </div>

            <div class="form-group">
              <label>Descrição</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Descrição do produto"
              ></textarea>
              <small class="help-text"
                >Tags permitidas: &lt;p&gt;, &lt;br&gt;, &lt;b&gt;,
                &lt;strong&gt;</small
              >
            </div>

            <div class="form-group">
              <label>Imagens (Máx. 4)</label>
              <div class="image-upload-area" @click="$refs.imageInput.click()">
                <input
                  type="file"
                  ref="imageInput"
                  multiple
                  accept="image/jpeg, image/png"
                  @change="handleImageUpload"
                  :disabled="totalImages >= 4"
                  style="display: none"
                />
                <div class="upload-placeholder" v-if="totalImages === 0">
                  <span>📷</span>
                  <p>Clique para adicionar imagens</p>
                  <small>JPG ou PNG - Máximo 4 imagens</small>
                </div>
              </div>

              <div class="image-preview-grid" v-if="totalImages > 0">
                <!-- Imagens Existentes -->
                <div
                  v-for="(img, index) in existingImages"
                  :key="'exist-' + img.id"
                  class="image-preview-item"
                >
                  <img :src="img.url" :alt="'Imagem ' + img.id" />
                  <button
                    type="button"
                    class="btn-remove-image"
                    @click.stop="deleteExistingImage(img.id, index)"
                  >
                    ✕
                  </button>
                  <span class="image-order">Salva</span>
                </div>
                <!-- Novas Imagens -->
                <div
                  v-for="(img, index) in form.images"
                  :key="'new-' + index"
                  class="image-preview-item"
                >
                  <img :src="img.preview" :alt="'Nova Imagem'" />
                  <button
                    type="button"
                    class="btn-remove-image"
                    @click.stop="removeImage(index)"
                  >
                    ✕
                  </button>
                  <span class="image-order">{{ index + 1 }}</span>
                </div>
              </div>
              <small v-if="totalImages >= 4" class="help-text warning"
                >Limite de 4 imagens atingido.</small
              >
            </div>

            <div class="form-group">
              <label>Status</label>
              <select v-model="form.active">
                <option :value="true">Ativo</option>
                <option :value="false">Inativo</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="closeModal">
            Cancelar
          </button>
          <!-- Botão chama handleSubmit diretamente -->
          <button
            type="button"
            class="btn-save"
            @click="handleSubmit"
            :disabled="loading || isFormInvalid"
          >
            {{ loading ? "Salvando..." : isEdit ? "Atualizar" : "Cadastrar" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from "vue";
import Sidebar from "../components/Sidebar.vue";
import { productService } from "@/services/product";
import Swal from "sweetalert2";

// --- Estados da Página ---
const products = ref([]);
const loading = ref(false);
const showFilters = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const priceError = ref("");

// --- Filtros e Paginação ---
const filters = reactive({
  title: "",
  active: undefined,
  price_min: null,
  price_max: null,
  page: 1,
});

const pagination = reactive({ current_page: 1, last_page: 1, total: 0 });

// --- Formulário ---
const form = reactive({
  id: null,
  title: "",
  cost: null,
  price: null,
  description: "",
  active: true,
  images: [],
});

const existingImages = ref([]); // Imagens que já estão no banco

// --- Computed: Total de Imagens e Validação do Botão ---
const totalImages = computed(
  () => form.images.length + existingImages.value.length,
);

const isFormInvalid = computed(() => {
  // Botão desabilitado se faltar dados OU se tiver erro de preço
  return (
    !form.title || form.cost == null || form.price == null || !!priceError.value
  );
});

// --- Métodos de Filtro ---
const toggleFilters = () => (showFilters.value = !showFilters.value);

const clearFilters = () => {
  filters.title = "";
  filters.active = undefined;
  filters.price_min = null;
  filters.price_max = null;
  applyFilters();
};

const applyFilters = async () => {
  filters.page = 1;
  await loadProducts();
};

// Carregar Produtos
const loadProducts = async () => {
  loading.value = true;

  try {
    const response = await productService.getAll({
      title: filters.title,
      active: filters.active,
      price_min: filters.price_min,
      price_max: filters.price_max,
      page: filters.page,
    });

    const paginationObj = response;

    products.value = paginationObj.data || [];
    pagination.current_page = paginationObj.current_page || 1;
    pagination.last_page = paginationObj.last_page || 1;
    pagination.total = paginationObj.total || 0;
  } catch (error) {
    console.error("Erro ao carregar:", error);
    showToast("error", error.message);
  } finally {
    loading.value = false;
  }
};

const changePage = async (page) => {
  if (page < 1 || page > pagination.last_page) return;
  filters.page = page;
  await loadProducts();
};

const goToPage = (page) => {
  const pageNum = parseInt(page, 10);
  if (!isNaN(pageNum) && pageNum >= 1 && pageNum <= pagination.last_page) {
    changePage(pageNum);
  }
};

// --- Modal e Formulário ---
const openModal = (mode, product = null) => {
  isEdit.value = mode === "edit";

  if (isEdit.value && product) {
    form.id = product.id;
    form.title = product.title;
    form.cost = product.cost;
    form.price = product.price;
    form.description = product.description || "";
    form.active = product.active;
    form.images = []; // Limpa novas imagens
    existingImages.value = product.images || []; // Carrega imagens salvas
  } else {
    resetForm();
  }
  priceError.value = "";
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.id = null;
  form.title = "";
  form.cost = null;
  form.price = null;
  form.description = "";
  form.active = true;
  form.images = [];
  existingImages.value = [];
  priceError.value = "";
};

// --- Validação de Preço (Regra de Negócio) ---
const validatePrice = () => {
  if (form.cost != null && form.price != null) {
    const minPrice = form.cost * 1.1;
    if (form.price < minPrice) {
      priceError.value = `Mínimo: ${formatCurrency(minPrice)} (Custo + 10%)`;
      return false;
    }
  }
  priceError.value = "";
  return true;
};

// --- Upload de Imagens ---
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  const remainingSlots = 4 - totalImages.value;

  if (files.length > remainingSlots) {
    showToast(
      "warning",
      `Você pode adicionar apenas ${remainingSlots} imagem(s).`,
    );
    return;
  }

  files.forEach((file) => {
    if (!["image/jpeg", "image/png"].includes(file.type)) {
      showToast("error", "Apenas JPG e PNG.");
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      showToast("error", "Máximo 2MB por imagem.");
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      form.images.push({ file, preview: e.target.result });
    };
    reader.readAsDataURL(file);
  });
  event.target.value = ""; // Limpa input para permitir re-seleção
};

const removeImage = (index) => form.images.splice(index, 1);

const deleteExistingImage = async (imageId, index) => {
  const result = await Swal.fire({
    title: "Remover imagem?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sim",
  });
  if (result.isConfirmed) {
    try {
      await productService.deleteImage(form.id, imageId);
      existingImages.value.splice(index, 1);
      showToast("success", "Imagem removida.");
    } catch (error) {
      showToast("error", "Erro ao remover.");
    }
  }
};

// --- Submit (Create/Update) ---
const handleSubmit = async () => {
  console.log("Iniciando submit...", { form, isEdit: isEdit.value });

  // 1. Validar Preço
  if (!validatePrice()) {
    showToast("error", "Corrija o preço antes de continuar.");
    return;
  }

  loading.value = true;

  try {
    const formData = {
      title: form.title,
      cost: form.cost,
      price: form.price,
      description: form.description,
      active: form.active === true ? 1 : 0,
    };
    const files = form.images.map((img) => img.file);

    if (isEdit.value) {
      await productService.update(form.id, formData, files);
      showToast("success", "Produto atualizado!");
    } else {
      await productService.create(formData, files);
      showToast("success", "Produto cadastrado!");
    }

    closeModal();
    await loadProducts();
  } catch (error) {
    console.error("Erro no submit:", error);
    showToast("error", error.message);
  } finally {
    loading.value = false;
  }
};

// --- Ações da Tabela ---
const deactivateProduct = async (id) => {
  const result = await Swal.fire({
    title: "Desativar?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sim",
  });
  if (result.isConfirmed) {
    try {
      await productService.deactivate(id);
      showToast("success", "Produto desativado!");
      loadProducts();
    } catch (error) {
      showToast("error", error.message);
    }
  }
};

const activateProduct = async (id) => {
  const result = await Swal.fire({
    title: "Ativar?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sim",
  });
  if (result.isConfirmed) {
    try {
      await productService.reactivate(id);
      showToast("success", "Produto ativado!");
      loadProducts();
    } catch (error) {
      showToast("error", error.message);
    }
  }
};

// --- Utilitários ---
const formatCurrency = (value) => {
  const number = Number(value);
  if (isNaN(number)) return "R$ 0,00";
  return number.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
};

const showToast = (icon, message) => {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon,
    title: message,
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });
};

onMounted(() => {
  loadProducts();
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.page-wrapper {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
}

.main-content {
  flex: 1;
  margin-left: 260px;
  min-height: 100vh;
}

.header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header h1 {
  font-size: 24px;
  color: #1f2937;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-filter {
  padding: 10px 20px;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-filter:hover {
  background: #667eea;
  color: white;
}

.btn-create {
  padding: 10px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-create:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.container {
  padding: 30px;
}

.filters-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 25px;
  display: none;
}

.filters-card.show {
  display: block;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters-header h3 {
  font-size: 18px;
  color: #1f2937;
  margin: 0;
}

.btn-close-filters {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 20px;
  padding: 5px;
  transition: all 0.2s;
}

.btn-close-filters:hover {
  color: #ef4444;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.filter-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filters-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-clear-filters {
  padding: 10px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-clear-filters:hover {
  background: #f9fafb;
}

.btn-apply-filters {
  padding: 10px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-apply-filters:hover {
  background: #5568d3;
}

.products-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f9fafb;
}

th {
  padding: 15px 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
}

td {
  padding: 15px 12px;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  color: #6b7280;
}

tbody tr {
  transition: all 0.2s;
}

tbody tr:hover {
  background: #f9fafb;
}

.product-image-placeholder {
  width: 50px;
  height: 50px;
  background: #f3f4f6;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  overflow: hidden;
}

.product-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-active {
  background: #d1fae5;
  color: #065f46;
}

.badge-inactive {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-edit {
  padding: 6px 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: #2563eb;
}

.btn-deactivate {
  padding: 6px 12px;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-deactivate:hover {
  background: #d97706;
}

.btn-activate {
  padding: 6px 12px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-activate:hover {
  background: #059669;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 25px;
}

.pagination-arrow {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  cursor: pointer;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
  color: #6b7280;
  line-height: 1;
}

.pagination-arrow:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
}

.pagination-arrow:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-status {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #6b7280;
}

.pagination-input {
  width: 45px;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  text-align: center;
  font-size: 14px;
  margin-right: 8px;
}

.pagination-input::-webkit-outer-spin-button,
.pagination-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.pagination-input:focus {
  outline: none;
  border-color: #667eea;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #9ca3af;
  font-size: 14px;
}

/* ==================== MODAL ==================== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  font-size: 20px;
  color: #1f2937;
  margin: 0;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 24px;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-close-modal:hover {
  color: #ef4444;
}

.modal-body {
  padding: 25px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 25px;
  border-top: 1px solid #e5e7eb;
}

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.help-text {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #6b7280;
}

.help-text.warning {
  color: #f59e0b;
}

.error-message {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #ef4444;
}

.input-error {
  border-color: #ef4444 !important;
}

.image-upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.image-upload-area:hover {
  border-color: #667eea;
  background: #f9fafb;
}

.upload-placeholder {
  color: #6b7280;
}

.upload-placeholder span {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}

.upload-placeholder p {
  font-size: 14px;
  margin-bottom: 4px;
}

.upload-placeholder small {
  font-size: 12px;
  color: #9ca3af;
}

.image-preview-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-top: 15px;
}

.image-preview-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.image-preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.btn-remove-image {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-remove-image:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.image-order {
  position: absolute;
  bottom: 4px;
  left: 4px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 4px;
}

.btn-cancel {
  padding: 10px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #5568d3;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ==================== RESPONSIVO ==================== */
@media (max-width: 1024px) {
  .main-content {
    margin-left: 70px;
  }

  .modal-container {
    width: 95%;
    max-width: 500px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .table-container {
    overflow-x: auto;
  }

  table {
    min-width: 700px;
  }

  .modal-container {
    width: 100%;
    max-height: 95vh;
  }

  .image-preview-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .header h1 {
    font-size: 20px;
  }

  .btn-filter,
  .btn-create {
    padding: 8px 16px;
    font-size: 13px;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 15px;
  }

  .image-preview-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
