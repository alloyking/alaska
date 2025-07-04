
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount, flushPromises } from '@vue/test-utils';
import ArchiveRecipes from '../ArchiveRecipes.vue';
import RecipeSearch from '../RecipeSearch.vue';
import RecipeCard from '../RecipeCard.vue';
import axios from 'axios';
import { ref } from 'vue';
// Mock axios
vi.mock('axios');

// Add global axios to component
vi.stubGlobal('axios', axios);

describe('ArchiveRecipes.vue', () => {
  let wrapper;

  const mockRecipes = [
    {
      id: 1,
      name: 'Pasta Carbonara',
      author: { name: 'Chef John', email: 'john@example.com' },
      description: 'Classic Italian pasta dish',
      ingredients: [
        { name: 'spaghetti', quantity: '200g' },
        { name: 'eggs', quantity: '2' }
      ],
      steps: [
        { step_order: 1, instruction: 'Boil pasta' },
        { step_order: 2, instruction: 'Mix eggs and cheese' }
      ]
    },
    {
      id: 2,
      name: 'Caesar Salad',
      author: { name: 'Chef Mary', email: 'mary@example.com' },
      description: 'Fresh salad with Caesar dressing',
      ingredients: [
        { name: 'romaine lettuce', quantity: '1 head' },
        { name: 'croutons', quantity: '1 cup' }
      ],
      steps: [
        { step_order: 1, instruction: 'Wash lettuce' },
        { step_order: 2, instruction: 'Add dressing' }
      ]
    }
  ];

  const mockResponse = {
    data: {
      data: mockRecipes,
      links: {
        first: 'http://api.test/recipes?page=1',
        last: 'http://api.test/recipes?page=5',
        prev: null,
        next: 'http://api.test/recipes?page=2'
      }
    }
  };

  beforeEach(() => {
    // Reset mocks
    vi.resetAllMocks();

    // Setup axios mock to return our test data
    axios.get.mockResolvedValue(mockResponse);
  });

  it('renders the component correctly', async () => {
    wrapper = shallowMount(ArchiveRecipes);

    // Wait for the component to finish loading data
    await flushPromises();

    expect(wrapper.exists()).toBe(true);
    expect(wrapper.findComponent(RecipeSearch).exists()).toBe(true);

    // Use a different selector for RecipeCard that matches the component's structure
    const recipeItems = wrapper.findAll('li');
    expect(recipeItems.length).toBe(2);
  });

  it('shows loading state initially', async () => {
    // Create a delayed promise to test loading state
    let resolvePromise;
    const delayedPromise = new Promise(resolve => {
      resolvePromise = resolve;
    });

    axios.get.mockReturnValueOnce(delayedPromise);

    wrapper = shallowMount(ArchiveRecipes);

    // Should be in loading state initially (before promise resolves)
    expect(wrapper.find('.animate-pulse').exists()).toBe(true);

    // Resolve the promise to complete loading
    resolvePromise(mockResponse);
    await flushPromises();

    // Should not be in loading state anymore
    expect(wrapper.find('.animate-pulse').exists()).toBe(false);
  });

  it('displays an error message when API call fails', async () => {
    const errorMessage = 'Network Error';
    axios.get.mockRejectedValueOnce({
      message: errorMessage
    });

    wrapper = shallowMount(ArchiveRecipes);
    await flushPromises();

    expect(wrapper.find('.text-red-600').text()).toContain(errorMessage);
  });

  it('handles search correctly', async () => {
    wrapper = shallowMount(ArchiveRecipes);
    await flushPromises();

    // Reset mock to verify new call
    axios.get.mockClear();

    const searchFilters = {
      keyword: 'pasta',
      authorEmail: 'john@example.com',
      ingredient: 'eggs'
    };

    // Trigger search event
    await wrapper.findComponent(RecipeSearch).vm.$emit('search', searchFilters);

    // Need to wait a tick for the async handler to execute
    await flushPromises();

    expect(axios.get).toHaveBeenCalledWith('/api/recipes', {
      params: {
        page: 1,
        keyword: 'pasta',
        author_email: 'john@example.com',
        ingredient: 'eggs'
      }
    });
  });

  it('handles pagination correctly', async () => {
    wrapper = shallowMount(ArchiveRecipes);
    await flushPromises();
    // Reset mock to verify new call
    axios.get.mockClear();

    // Find the Next button by its text content
    const nextButton = wrapper.findAll('button').find(btn => btn.text() === 'Next');
    expect(nextButton).toBeTruthy(); // Verify button exists
    expect(nextButton.text()).toBe('Next'); // Verify it's the Next button

    await nextButton.trigger('click');

    // Verify the correct page was requested
    expect(axios.get).toHaveBeenCalledWith(expect.any(String), {
      params: expect.objectContaining({
        page: 2
      })
    });
  });

  it('shows a message when no recipes are found', async () => {
    // Mock empty response
    axios.get.mockResolvedValueOnce({
      data: {
        data: [],
        links: {
          first: null,
          last: null,
          prev: null,
          next: null
        }
      }
    });

    wrapper = shallowMount(ArchiveRecipes);
    await flushPromises();

    expect(wrapper.find('.text-gray-500').text()).toBe('No recipes found matching your criteria.');
  });
});
